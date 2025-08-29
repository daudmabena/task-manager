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
        Permission::firstOrCreate(['name' => 'view tasks']);
        Permission::firstOrCreate(['name' => 'create tasks']);
        Permission::firstOrCreate(['name' => 'edit tasks']);
        Permission::firstOrCreate(['name' => 'delete tasks']);
        Permission::firstOrCreate(['name' => 'manage users']);
        Permission::firstOrCreate(['name' => 'view activity logs']);
        Permission::firstOrCreate(['name' => 'view audits']);
        Permission::firstOrCreate(['name' => 'create systems']);
        Permission::firstOrCreate(['name' => 'edit systems']);
        Permission::firstOrCreate(['name' => 'view systems']);
        Permission::firstOrCreate(['name' => 'delete systems']);

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
