<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\StockBalance;
use App\Models\StockReservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_stock(): void
    {
        $this->get('/stock')->assertRedirect('/login');
    }

    public function test_authorized_user_can_add_stock_and_negative_stock_is_rejected(): void
    {
        [$user, $product] = $this->authorizedUserAndProduct();
        $token = csrf_token();

        $this->actingAs($user)->post('/stock', [
            '_token' => $token,
            'product_id' => $product->id,
            'quantity' => '5.250',
            'type' => 'in',
            'reason' => 'Penerimaan bahan',
        ])->assertRedirect('/stock');

        $this->assertDatabaseHas('stock_balances', ['product_id' => $product->id, 'physical_quantity' => '5.250']);
        $this->assertDatabaseHas('stock_movements', ['product_id' => $product->id, 'quantity' => '5.250', 'type' => 'in']);

        $this->actingAs($user)->post('/stock', [
            '_token' => $token,
            'product_id' => $product->id,
            'quantity' => '6',
            'type' => 'out',
            'reason' => 'Pengeluaran bahan',
        ])->assertSessionHasErrors('stock');
    }

    public function test_cancelling_order_releases_reserved_stock(): void
    {
        [$user, $product] = $this->authorizedUserAndProduct(['create-orders']);
        StockBalance::create(['product_id' => $product->id, 'physical_quantity' => '4.000', 'reserved_quantity' => '1.500']);
        $order = Order::create(['created_by' => $user->id, 'document_type' => 'pos', 'document_number' => 'POS-TEST', 'status' => 'draft', 'subtotal' => '100.00', 'discount_amount' => '0.00', 'tax_amount' => '9.91', 'total' => '100.00']);
        StockReservation::create(['product_id' => $product->id, 'order_id' => $order->id, 'quantity' => '1.500', 'status' => 'reserved']);

        $this->actingAs($user)->post("/orders/{$order->id}/cancel", ['_token' => csrf_token()])->assertRedirect();

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'cancelled']);
        $this->assertDatabaseHas('stock_balances', ['product_id' => $product->id, 'reserved_quantity' => '0.000']);
        $this->assertDatabaseHas('stock_reservations', ['order_id' => $order->id, 'status' => 'cancelled']);
    }

    public function test_order_reserves_available_stock(): void
    {
        [$user, $product] = $this->authorizedUserAndProduct(['create-orders']);
        StockBalance::create(['product_id' => $product->id, 'physical_quantity' => '4.000', 'reserved_quantity' => '0']);

        $this->actingAs($user)->post('/orders', [
            '_token' => csrf_token(),
            'document_type' => 'pos',
            'items' => [['product_id' => $product->id, 'quantity' => '1.500']],
            'discount_value' => '0',
        ])->assertRedirect('/orders');

        $this->assertDatabaseHas('stock_reservations', ['product_id' => $product->id, 'quantity' => '1.500', 'status' => 'reserved']);
        $this->assertDatabaseHas('stock_balances', ['product_id' => $product->id, 'reserved_quantity' => '1.500']);
    }

    /** @param list<string> $permissionNames */
    private function authorizedUserAndProduct(array $permissionNames = ['manage-inventory']): array
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => fake()->unique()->word(), 'label' => 'Test']);
        foreach ($permissionNames as $permissionName) {
            $permission = Permission::create(['name' => $permissionName, 'label' => $permissionName]);
            $role->permissions()->attach($permission);
        }
        $user->roles()->attach($role);
        $product = Product::create([
            'name' => 'Kue Test',
            'code' => fake()->unique()->bothify('KUE-###'),
            'unit' => 'pcs',
            'store_price' => '10000.00',
            'hotel_price' => '11000.00',
            'is_active' => true,
        ]);

        return [$user, $product];
    }
}
