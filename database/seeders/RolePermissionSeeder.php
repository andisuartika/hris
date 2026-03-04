<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Buat daftar Permission
        $permissions = [
            'view attendances',
            'manage attendances',
            'manage employees',
            'view own attendance',
            'clock in out',
            'manage settings'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 2. Buat Role dan Assign Permissions
        $employeeRole = Role::firstOrCreate(['name' => 'employee']);
        $employeeRole->syncPermissions(['view own attendance', 'clock in out']);

        $hrRole = Role::firstOrCreate(['name' => 'hr']);
        $hrRole->syncPermissions(['view attendances', 'manage attendances', 'manage employees']);

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions(Permission::all()); // Admin dapat semua akses
    }
}
