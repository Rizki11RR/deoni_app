<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardMenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_contains_available_menu_links(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'admin', 'label' => 'Admin']);
        $permission = Permission::create(['name' => 'manage-users', 'label' => 'Kelola pengguna']);
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        $this->actingAs($user)->get('/dashboard')->assertOk()->assertSee(route('users.index'))->assertSee('Pengguna');
    }
}
