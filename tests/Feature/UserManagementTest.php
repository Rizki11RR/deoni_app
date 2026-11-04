<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_open_user_list(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'admin', 'label' => 'Admin']);
        $permission = Permission::create(['name' => 'manage-users', 'label' => 'Kelola pengguna']);
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        $this->actingAs($user)->get('/users')->assertOk();
    }

    public function test_authenticated_user_can_create_user_with_roles(): void
    {
        $admin = User::factory()->create();
        $adminRole = Role::create(['name' => 'admin', 'label' => 'Admin']);
        $permission = Permission::create(['name' => 'manage-users', 'label' => 'Kelola pengguna']);
        $adminRole->permissions()->attach($permission);
        $admin->roles()->attach($adminRole);
        $role = Role::create(['name' => 'cashier', 'label' => 'Kasir']);

        $this->actingAs($admin)->post('/users', [
            'name' => 'Kasir Satu',
            'email' => 'kasir@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => [$role->id],
        ])->assertRedirect('/users');

        $this->assertDatabaseHas('users', ['email' => 'kasir@example.com']);
        $this->assertDatabaseHas('role_user', ['role_id' => $role->id]);
    }
}
