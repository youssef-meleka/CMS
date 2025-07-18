<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@cms.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make("Password0!"),
                'is_active' => true,
            ]
        );
        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        // Create manager user
        $manager = User::firstOrCreate(
            ['email' => 'manager@cms.com'],
            [
                'name' => 'Manager User',
                'password' => Hash::make("Password0!"),
                'is_active' => true,
            ]
        );
        if (!$manager->hasRole('manager')) {
            $manager->assignRole('manager');
        }

        // Create employee users
        $employee1 = User::firstOrCreate(
            ['email' => 'employee1@cms.com'],
            [
                'name' => 'Employee One',
                'password' => Hash::make("Password0!"),
                'is_active' => true,
            ]
        );
        if (!$employee1->hasRole('employee')) {
            $employee1->assignRole('employee');
        }

        $employee2 = User::firstOrCreate(
            ['email' => 'employee2@cms.com'],
            [
                'name' => 'Employee Two',
                'password' => Hash::make("Password0!"),
                'is_active' => true,
            ]
        );
        if (!$employee2->hasRole('employee')) {
            $employee2->assignRole('employee');
        }

        // Create some customer users
        $customer1 = User::firstOrCreate(
            ['email' => 'john@example.com'],
            [
                'name' => 'John Doe',
                'password' => Hash::make("Password0!"),
                'is_active' => true,
            ]
        );
        if (!$customer1->hasRole('customer')) {
            $customer1->assignRole('customer');
        }

        $customer2 = User::firstOrCreate(
            ['email' => 'jane@example.com'],
            [
                'name' => 'Jane Smith',
                'password' => Hash::make("Password0!"),
                'is_active' => true,
            ]
        );
        if (!$customer2->hasRole('customer')) {
            $customer2->assignRole('customer');
        }

        $customer3 = User::firstOrCreate(
            ['email' => 'bob@example.com'],
            [
                'name' => 'Bob Johnson',
                'password' => Hash::make("Password0!"),
                'is_active' => true,
            ]
        );
        if (!$customer3->hasRole('customer')) {
            $customer3->assignRole('customer');
        }
    }
}
