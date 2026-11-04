<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_is_rejected_for_cancelled_order(): void
    {
        $user = User::factory()->create();
        $permission = Permission::create(['name' => 'manage-payments', 'label' => 'Kelola pembayaran']);
        $role = Role::create(['name' => 'cashier', 'label' => 'Kasir']);
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);
        $order = Order::create(['created_by' => $user->id, 'document_type' => 'pos', 'document_number' => 'PAY-CANCELLED', 'status' => 'cancelled', 'preparation_status' => 'pending', 'payment_status' => 'unpaid', 'subtotal' => 100, 'discount_amount' => 0, 'tax_amount' => 0, 'total' => 100]);

        $this->actingAs($user)->post("/orders/{$order->id}/payments", ['amount' => 10, 'method' => 'cash'])->assertRedirect();
        $this->assertDatabaseMissing('payments', ['order_id' => $order->id]);
    }

    public function test_payment_updates_order_status_for_partial_and_full_payment(): void
    {
        $user = User::factory()->create();
        $permission = Permission::create(['name' => 'manage-payments', 'label' => 'Kelola pembayaran']);
        $role = Role::create(['name' => 'cashier', 'label' => 'Kasir']);
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);
        $order = Order::create(['created_by' => $user->id, 'document_type' => 'pos', 'document_number' => 'PAY-STATUS', 'status' => 'draft', 'preparation_status' => 'pending', 'payment_status' => 'unpaid', 'subtotal' => '100.00', 'discount_amount' => '0.00', 'tax_amount' => '9.91', 'total' => '100.00']);

        $this->actingAs($user)->post("/orders/{$order->id}/payments", ['_token' => csrf_token(), 'amount' => '40', 'method' => 'cash'])->assertRedirect();
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_status' => 'partially_paid']);
        $this->actingAs($user)->post("/orders/{$order->id}/payments", ['_token' => csrf_token(), 'amount' => '60', 'method' => 'qris'])->assertRedirect();
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'payment_status' => 'paid']);
    }
}
