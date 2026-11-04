<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashierAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_can_approve_bon_but_cannot_manage_inventory(): void
    {
        $user = $this->userWithPermissions(['create-orders', 'approve-bons', 'manage-payments']);

        $this->actingAs($user)->get('/orders/create')->assertOk();
        $this->actingAs($user)->get('/stock')->assertForbidden();
    }

    public function test_user_without_cashier_permission_cannot_approve_bon(): void
    {
        $user = $this->userWithPermissions(['create-orders']);
        $order = Order::create(['created_by' => $user->id, 'document_type' => 'store', 'document_number' => 'BON-1', 'status' => 'draft', 'preparation_status' => 'pending', 'subtotal' => 10, 'discount_amount' => 0, 'tax_amount' => 0, 'total' => 10]);

        $this->actingAs($user)->post("/orders/{$order->id}/approve")->assertForbidden();
    }

    /** @param list<string> $permissions */
    private function userWithPermissions(array $permissions): User
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'cashier-'.uniqid(), 'label' => 'Kasir']);
        foreach ($permissions as $permission) {
            $rolePermission = Permission::firstOrCreate(['name' => $permission], ['label' => $permission]);
            $role->permissions()->attach($rolePermission);
        }
        $user->roles()->attach($role);

        return $user;
    }
}
