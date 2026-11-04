<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErrorHandlingTest extends TestCase
{
    use RefreshDatabase;

    public function test_missing_page_returns_indonesian_not_found_page(): void
    {
        $this->get('/halaman-tidak-ada')->assertNotFound()->assertSee('Halaman tidak ditemukan');
    }

    public function test_unauthorized_user_receives_forbidden_page(): void
    {
        $user = User::factory()->create();
        $permission = Permission::create(['name' => 'manage-users', 'label' => 'Kelola pengguna']);
        Role::create(['name' => 'sales', 'label' => 'Penjualan'])->permissions()->attach($permission);

        $this->actingAs($user)->get('/users')->assertForbidden()->assertSee('Akses ditolak');
    }
}
