<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'access dashboard',
            'manage users',
            'manage products',
            'manage orders',
            'view products',
            'view orders',
            'view own orders',
            'create products',
            'edit products',
            'delete products',
            'create orders',
            'edit orders',
            'delete orders',
            'update order status',
            'assign orders',
            'view statistics',
            'manage product stock',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions

        // Admin role - full access
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(Permission::all());

        // Manager role - product and order management
        $manager = Role::firstOrCreate(['name' => 'manager']);
        $manager->syncPermissions([
            'access dashboard',
            'manage products',
            'manage orders',
            'view products',
            'view orders',
            'create products',
            'edit products',
            'delete products',
            'create orders',
            'edit orders',
            'delete orders',
            'update order status',
            'assign orders',
            'view statistics',
            'manage product stock',
        ]);

        // Employee role - basic access
        $employee = Role::firstOrCreate(['name' => 'employee']);
        $employee->syncPermissions([
            'view products',
            'view orders',
            'view own orders',
        ]);

        // Customer role - minimal access for future use
        $customer = Role::firstOrCreate(['name' => 'customer']);
        $customer->syncPermissions([
            'view products',
            'view own orders',
        ]);
    }
}
