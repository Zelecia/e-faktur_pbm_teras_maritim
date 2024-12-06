<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define permissions
        $permissions = [
            'tambah-user',
            'edit-user',
            'hapus-user',
            'lihat-user',
            'tambah-data',
            'edit-data',
            'hapus-data',
            'lihat-data'
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles
        $adminRole = Role::firstOrCreate(['name' => 'Administrator']);
        $staffRole = Role::firstOrCreate(['name' => 'Karyawan']);

        // Assign permissions to admin role
        $adminPermissions = ['tambah-user', 'edit-user', 'hapus-user', 'lihat-user'];
        $adminRole->syncPermissions($adminPermissions);

        // Assign the 'admin' role to an existing user with ID 1
        $adminUser = User::firstOrCreate([
            'name' => 'Administrator',
            'email' => 'admin@pbmterasmaritim.com'
        ], [
            'password' => Hash::make('12345678') // Hash the password
        ]);

        if ($adminUser) {
            $adminUser->assignRole($adminRole);
        }

        // Create a new user with 'staff' role
        $staffUser = User::firstOrCreate([
            'name' => 'Staff',
            'email' => 'staff@pbmterasmaritim.com'
        ], [
            'password' => Hash::make('12345678') // Hash the password
        ]);

        // Assign the 'staff' role to the newly created user
        $staffUser->assignRole($staffRole);
    }
}
