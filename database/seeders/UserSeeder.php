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
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@cms.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Create manager user
        User::create([
            'name' => 'Manager User',
            'email' => 'manager@cms.com',
            'password' => Hash::make('password'),
            'role' => 'manager',
            'is_active' => true,
        ]);

        // Create employee users
        User::create([
            'name' => 'Employee One',
            'email' => 'employee1@cms.com',
            'password' => Hash::make('password'),
            'role' => 'employee',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Employee Two',
            'email' => 'employee2@cms.com',
            'password' => Hash::make('password'),
            'role' => 'employee',
            'is_active' => true,
        ]);

        // Create some customer users
        User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password'),
            'role' => 'employee',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => Hash::make('password'),
            'role' => 'employee',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Bob Johnson',
            'email' => 'bob@example.com',
            'password' => Hash::make('password'),
            'role' => 'employee',
            'is_active' => true,
        ]);
    }
}
