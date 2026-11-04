<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_user_can_view_audit_logs(): void
    {
        $user = User::factory()->create();
        $permission = Permission::create(['name' => 'manage-users', 'label' => 'Kelola pengguna']);
        $role = Role::create(['name' => 'admin', 'label' => 'Admin']);
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);
        AuditLog::create(['user_id' => $user->id, 'action' => 'request', 'method' => 'GET', 'path' => 'dashboard', 'payload' => ['safe' => true], 'status_code' => 200]);

        $this->actingAs($user)->get('/audit-logs')->assertOk()->assertSee('Log audit')->assertSee('dashboard');
    }

    public function test_user_without_permission_cannot_view_audit_logs(): void
    {
        $this->actingAs(User::factory()->create())->get('/audit-logs')->assertForbidden();
    }
}
