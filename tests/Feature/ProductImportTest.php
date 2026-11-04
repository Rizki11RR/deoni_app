<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ProductImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_csv_can_be_previewed_and_imported(): void
    {
        $user = $this->userWithProductPermission();
        $csv = UploadedFile::fake()->createWithContent('products.csv', "name,code,unit,store_price,hotel_price\nTepung,TP-001,kg,10000,11000\n");

        $this->actingAs($user)->post('/products/import/preview', ['_token' => csrf_token(), 'file' => $csv])->assertOk()->assertSee('Tepung');
        $this->actingAs($user)->post('/products/import', ['_token' => csrf_token()])->assertRedirect('/products');
        $this->assertDatabaseHas('products', ['code' => 'TP-001']);
    }

    public function test_invalid_csv_shows_alert_and_does_not_import(): void
    {
        $user = $this->userWithProductPermission();
        $csv = UploadedFile::fake()->createWithContent('products.csv', "name,code\nTepung,TP-001\n");

        $this->actingAs($user)->post('/products/import/preview', ['_token' => csrf_token(), 'file' => $csv])->assertSessionHasErrors('file');
        $this->assertDatabaseCount('products', 0);
    }

    private function userWithProductPermission(): User
    {
        $user = User::factory()->create();
        $permission = Permission::create(['name' => 'manage-products', 'label' => 'Kelola produk']);
        $role = Role::create(['name' => 'importer', 'label' => 'Importer']);
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        return $user;
    }
}
