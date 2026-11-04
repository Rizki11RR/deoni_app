<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'admin' => 'Admin',
            'owner' => 'Owner',
            'cashier' => 'Kasir',
            'sales' => 'Penjualan Depan',
            'warehouse' => 'Gudang',
        ];

        foreach ($roles as $name => $label) {
            Role::firstOrCreate(['name' => $name], ['label' => $label]);
        }

        $permissions = [
            'manage-users' => 'Kelola pengguna',
            'manage-products' => 'Kelola produk',
            'manage-customers' => 'Kelola pelanggan',
            'create-orders' => 'Buat order',
            'manage-payments' => 'Kelola pembayaran',
            'manage-inventory' => 'Kelola stok',
            'view-reports' => 'Lihat laporan',
        ];

        foreach ($permissions as $name => $label) {
            Permission::firstOrCreate(['name' => $name], ['label' => $label]);
        }

        Role::where('name', 'admin')->firstOrFail()->permissions()->sync(Permission::all());
        Role::where('name', 'owner')->firstOrFail()->permissions()->sync(Permission::all());
        Role::where('name', 'cashier')->firstOrFail()->permissions()->sync(Permission::whereIn('name', ['create-orders', 'manage-payments', 'view-reports'])->pluck('id'));
        Role::where('name', 'sales')->firstOrFail()->permissions()->sync(Permission::whereIn('name', ['create-orders', 'manage-customers'])->pluck('id'));
        Role::where('name', 'warehouse')->firstOrFail()->permissions()->sync(Permission::whereIn('name', ['manage-inventory', 'create-orders'])->pluck('id'));
    }
}
