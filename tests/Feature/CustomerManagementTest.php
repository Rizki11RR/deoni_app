<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_user_can_create_customer(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'sales', 'label' => 'Penjualan']);
        $permission = Permission::create(['name' => 'manage-customers', 'label' => 'Kelola pelanggan']);
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        $this->actingAs($user)->post('/customers', [
            'name' => 'Toko Mawar',
            'price_type' => 'hotel',
        ])->assertRedirect('/customers');

        $this->assertDatabaseHas('customers', ['name' => 'Toko Mawar', 'price_type' => 'hotel']);
    }

    public function test_customer_factory_creates_customer(): void
    {
        Customer::factory()->create();

        $this->assertDatabaseCount('customers', 1);
    }
}
