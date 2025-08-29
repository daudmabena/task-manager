<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        Permission::create(['name' => 'view tasks']);
        Permission::create(['name' => 'create tasks']);
        Permission::create(['name' => 'edit tasks']);
        Permission::create(['name' => 'delete tasks']);
        Permission::create(['name' => 'manage users']);
        Permission::create(['name' => 'view activity logs']);
        Permission::create(['name' => 'view audits']);

        // Create roles and assign existing permissions
        $adminRole = Role::create(['name' => 'admin']);
        $managerRole = Role::create(['name' => 'manager']);
        $userRole = Role::create(['name' => 'user']);

        // Admin gets all permissions
        $adminRole->givePermissionTo(Permission::all());

        // Manager gets task management permissions
        $managerRole->givePermissionTo([
            'view tasks',
            'create tasks', 
            'edit tasks',
            'delete tasks',
            'view activity logs',
            'view audits',
        ]);

        // User gets basic task permissions
        $userRole->givePermissionTo([
            'view tasks',
            'create tasks',
            'edit tasks', // Can edit their own tasks (handled in policy)
        ]);
    }
}
