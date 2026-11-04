<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        $accounts = [
            'admin' => ['name' => 'Admin Cake Store', 'email' => 'admin@cakestore.test'],
            'owner' => ['name' => 'Owner Cake Store', 'email' => 'owner@cakestore.test'],
            'cashier' => ['name' => 'Kasir Cake Store', 'email' => 'cashier@cakestore.test'],
            'sales' => ['name' => 'Sales Cake Store', 'email' => 'sales@cakestore.test'],
            'warehouse' => ['name' => 'Gudang Cake Store', 'email' => 'warehouse@cakestore.test'],
        ];

        foreach ($accounts as $roleName => $account) {
            $user = User::updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => Hash::make('ChangeMe123!'),
                    'email_verified_at' => now(),
                ],
            );
            $user->roles()->syncWithoutDetaching([Role::where('name', $roleName)->firstOrFail()->id]);
        }
    }
}
