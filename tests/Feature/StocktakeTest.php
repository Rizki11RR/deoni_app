<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\StockBalance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StocktakeTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_user_can_reconcile_physical_stock(): void
    {
        [$user, $product] = $this->userAndProduct();
        StockBalance::create(['product_id' => $product->id, 'physical_quantity' => '10.000', 'reserved_quantity' => '2.000']);

        $this->actingAs($user)->post('/stocktakes', ['_token' => csrf_token(), 'product_id' => $product->id, 'counted_quantity' => '8.500', 'reason' => 'Hitung gudang'])->assertRedirect('/stocktakes');

        $this->assertDatabaseHas('stocktakes', ['product_id' => $product->id, 'expected_quantity' => '10.000', 'counted_quantity' => '8.500', 'difference_quantity' => '-1.500']);
        $this->assertDatabaseHas('stock_balances', ['product_id' => $product->id, 'physical_quantity' => '8.500', 'reserved_quantity' => '2.000']);
        $this->assertDatabaseHas('stock_movements', ['product_id' => $product->id, 'type' => 'adjustment', 'quantity' => '-1.500']);
    }

    private function userAndProduct(): array
    {
        $user = User::factory()->create();
        $permission = Permission::create(['name' => 'manage-inventory', 'label' => 'Kelola stok']);
        $role = Role::create(['name' => 'warehouse', 'label' => 'Gudang']);
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);
        $product = Product::create(['name' => 'Produk Stocktake', 'code' => 'ST-001', 'unit' => 'pcs', 'store_price' => '10000.00', 'hotel_price' => '11000.00', 'is_active' => true]);

        return [$user, $product];
    }
}
