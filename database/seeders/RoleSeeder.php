<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            'view complaints',
            'create complaints',
            'update complaints',
            'delete complaints',
            'assign complaints',
            'resolve complaints',
            'view all complaints',
            'manage users',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        $technicianRole = Role::create(['name' => 'technician']);
        $technicianRole->givePermissionTo([
            'view complaints',
            'update complaints',
            'resolve complaints',
            'view all complaints',
        ]);

        $userRole = Role::create(['name' => 'user']);
        $userRole->givePermissionTo([
            'view complaints',
            'create complaints',
            'update complaints',
        ]);
    }
}
