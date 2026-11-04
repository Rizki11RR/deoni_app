<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PickingTest extends TestCase
{
    use RefreshDatabase;

    public function test_warehouse_user_can_update_preparation_status(): void
    {
        $user = User::factory()->create();
        $permission = Permission::create(['name' => 'create-orders', 'label' => 'Buat order']);
        $role = Role::create(['name' => 'warehouse', 'label' => 'Gudang']);
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);
        $order = Order::create(['created_by' => $user->id, 'document_type' => 'pos', 'document_number' => 'POS-PICK', 'status' => 'draft', 'preparation_status' => 'pending', 'subtotal' => '100.00', 'discount_amount' => '0.00', 'tax_amount' => '9.91', 'total' => '100.00']);

        $this->actingAs($user)->patch("/picking/{$order->id}", ['_token' => csrf_token(), 'preparation_status' => 'preparing'])->assertRedirect();
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'preparation_status' => 'preparing']);
    }

    public function test_cancelled_order_cannot_be_prepared(): void
    {
        $user = User::factory()->create();
        $permission = Permission::create(['name' => 'create-orders', 'label' => 'Buat order']);
        $role = Role::create(['name' => 'warehouse2', 'label' => 'Gudang']);
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);
        $order = Order::create(['created_by' => $user->id, 'document_type' => 'pos', 'document_number' => 'POS-CANCEL', 'status' => 'cancelled', 'preparation_status' => 'pending', 'subtotal' => '100.00', 'discount_amount' => '0.00', 'tax_amount' => '9.91', 'total' => '100.00']);

        $this->actingAs($user)->patch("/picking/{$order->id}", ['_token' => csrf_token(), 'preparation_status' => 'preparing'])->assertSessionHasErrors('picking');
    }
}
