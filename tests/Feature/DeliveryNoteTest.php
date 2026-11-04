<?php

namespace Tests\Feature;

use App\Models\DeliveryNote;
use App\Models\DocumentSequence;
use App\Models\Order;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryNoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_and_complete_delivery_note(): void
    {
        [$user, $order] = $this->userAndReadyOrder();
        $this->actingAs($user)->post('/delivery-notes', ['_token' => csrf_token(), 'order_id' => $order->id, 'delivery_method' => 'delivery', 'address' => 'Alamat pelanggan'])->assertRedirect('/delivery-notes');
        $note = DeliveryNote::first();
        $this->assertNotNull($note);
        $this->assertSame('SJ-000001', $note->document_number);
        $this->actingAs($user)->post("/delivery-notes/{$note->id}/deliver", ['_token' => csrf_token()])->assertRedirect();
        $this->assertDatabaseHas('delivery_notes', ['id' => $note->id, 'status' => 'delivered']);
    }

    public function test_delivery_requires_address(): void
    {
        [$user, $order] = $this->userAndReadyOrder();
        $this->actingAs($user)->post('/delivery-notes', ['_token' => csrf_token(), 'order_id' => $order->id, 'delivery_method' => 'delivery'])->assertSessionHasErrors('address');
    }

    private function userAndReadyOrder(): array
    {
        $user = User::factory()->create();
        $permission = Permission::create(['name' => 'create-orders', 'label' => 'Buat order']);
        $role = Role::create(['name' => 'delivery', 'label' => 'Pengiriman']);
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);
        $order = Order::create(['created_by' => $user->id, 'document_type' => 'pos', 'document_number' => 'POS-DLV', 'status' => 'draft', 'preparation_status' => 'ready', 'subtotal' => '100.00', 'discount_amount' => '0.00', 'tax_amount' => '9.91', 'total' => '100.00']);
        DocumentSequence::create(['document_type' => 'delivery', 'last_number' => 0]);

        return [$user, $order];
    }
}
