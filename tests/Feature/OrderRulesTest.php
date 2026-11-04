<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\DocumentSequence;
use App\Models\Order;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\StockBalance;
use App\Models\Uom;
use App\Models\UomCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_converts_sales_uom_quantity_to_base_quantity(): void
    {
        [$user, $product] = $this->orderUserAndProduct();
        $category = UomCategory::create(['name' => 'Weight', 'is_active' => true]);
        $uom = Uom::create(['uom_category_id' => $category->id, 'name' => 'Box', 'symbol' => 'box', 'conversion_factor' => 12, 'conversion_type' => 'multiply', 'precision' => 3, 'is_base' => true, 'is_active' => true]);
        $product->update(['uom_id' => $uom->id, 'sales_uom_id' => $uom->id]);
        StockBalance::create(['product_id' => $product->id, 'physical_quantity' => 24, 'reserved_quantity' => 0]);

        $this->actingAs($user)->post('/orders', ['document_type' => 'pos', 'items' => [['product_id' => $product->id, 'quantity' => 2]]])->assertRedirect();

        $this->assertDatabaseHas('order_items', ['base_quantity' => '24.000000', 'uom_factor' => '12.000000']);
    }

    public function test_order_rejects_inactive_uom_category(): void
    {
        [$user, $product] = $this->orderUserAndProduct();
        $category = UomCategory::create(['name' => 'Inactive', 'is_active' => false]);
        $uom = Uom::create(['uom_category_id' => $category->id, 'name' => 'Unit', 'symbol' => 'u', 'conversion_factor' => 1, 'conversion_type' => 'multiply', 'precision' => 3, 'is_base' => true, 'is_active' => true]);
        $product->update(['uom_id' => $uom->id]);
        StockBalance::create(['product_id' => $product->id, 'physical_quantity' => 10, 'reserved_quantity' => 0]);

        $this->actingAs($user)->post('/orders', ['document_type' => 'pos', 'items' => [['product_id' => $product->id, 'quantity' => 1]]])->assertSessionHasErrors('order');
    }

    public function test_document_sequence_generates_unique_next_number(): void
    {
        $sequence = DocumentSequence::create(['document_type' => 'pos', 'last_number' => 7]);
        $sequence->increment('last_number');
        $sequence->increment('last_number');
        $this->assertSame(9, $sequence->fresh()->last_number);
    }

    public function test_overdue_bon_remains_orderable_for_follow_up_order(): void
    {
        [$user, $product] = $this->orderUserAndProduct();
        Order::create(['created_by' => $user->id, 'document_type' => 'store', 'document_number' => 'BON-OLD', 'due_date' => now()->subDay()->toDateString(), 'status' => 'approved', 'preparation_status' => 'pending', 'subtotal' => 10, 'discount_amount' => 0, 'tax_amount' => 0, 'total' => 10]);
        StockBalance::create(['product_id' => $product->id, 'physical_quantity' => 10, 'reserved_quantity' => 0]);

        $this->actingAs($user)->post('/orders', ['document_type' => 'pos', 'items' => [['product_id' => $product->id, 'quantity' => 1]]])->assertRedirect();
        $this->assertDatabaseHas('orders', ['document_type' => 'pos']);
    }

    private function orderUserAndProduct(): array
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => uniqid('role-'), 'label' => 'Order']);
        $permission = Permission::create(['name' => 'create-orders', 'label' => 'Create orders']);
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);
        $category = Category::create(['name' => uniqid('category-'), 'is_active' => true]);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Product', 'code' => uniqid('P-'), 'unit' => 'pcs', 'store_price' => 10, 'hotel_price' => 10, 'is_active' => true]);

        return [$user, $product];
    }
}
