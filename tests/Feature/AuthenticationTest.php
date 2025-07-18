<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupRolesAndPermissions();
    }

    private function setupRolesAndPermissions(): void
    {
        // Create permissions
        $permissions = [
            'access dashboard', 'manage users', 'manage products', 'manage orders',
            'view products', 'view orders', 'view own orders', 'create products',
            'edit products', 'delete products', 'create orders', 'edit orders',
            'delete orders', 'update order status', 'assign orders', 'view statistics',
            'manage product stock',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(Permission::all());

        $manager = Role::firstOrCreate(['name' => 'manager']);
        $manager->syncPermissions([
            'access dashboard', 'manage products', 'manage orders', 'view products',
            'view orders', 'create products', 'edit products', 'delete products',
            'create orders', 'edit orders', 'delete orders', 'update order status',
            'assign orders', 'view statistics', 'manage product stock',
        ]);

        $employee = Role::firstOrCreate(['name' => 'employee']);
        $employee->syncPermissions([
            'view products', 'view own orders', 'create orders'
        ]);

        $customer = Role::firstOrCreate(['name' => 'customer']);
        $customer->syncPermissions([
            'view products', 'view own orders', 'create orders'
        ]);
    }

    /** @test */
    public function user_can_register()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password0!',
            'password_confirmation' => 'Password0!',
        ]);

        $response->assertStatus(201)
                ->assertJson([
                    'success' => true,
                    'message' => 'User registered successfully',
                ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'Test User',
        ]);

        // Check if user was assigned employee role
        $user = User::where('email', 'test@example.com')->first();
        $this->assertTrue($user->hasRole('employee'));
    }

    /** @test */
    public function user_can_register_with_specific_role()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
            'password' => 'Password0!',
            'password_confirmation' => 'Password0!',
            'role' => 'customer',
        ]);

        $response->assertStatus(201)
                ->assertJson([
                    'success' => true,
                    'message' => 'User registered successfully',
                ]);

        $user = User::where('email', 'customer@example.com')->first();
        $this->assertTrue($user->hasRole('customer'));
    }

    /** @test */
    public function user_can_login()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('Password0!'),
            'is_active' => true,
        ]);
        $user->assignRole('employee');

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'Password0!',
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Login successful',
                ])
                ->assertJsonStructure([
                    'data' => [
                        'user',
                        'token',
                    ],
                ]);
    }

    /** @test */
    public function user_cannot_login_with_invalid_credentials()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('Password0!'),
            'is_active' => true,
        ]);
        $user->assignRole('employee');

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'WrongPassword1!',
        ]);

        $response->assertStatus(422)
                ->assertJson([
                    'success' => false,
                ]);
    }

    /** @test */
    public function authenticated_user_can_access_account_endpoint()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('Password0!'),
            'is_active' => true,
        ]);
        $user->assignRole('employee');

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/auth/account');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'user' => [
                            'id' => $user->id,
                            'email' => $user->email,
                            'name' => $user->name,
                        ],
                    ],
                ])
                ->assertJsonStructure([
                    'data' => [
                        'user' => [
                            'id',
                            'name',
                            'email',
                            'role',
                            'roles',
                            'permissions',
                            'is_active',
                            'statistics',
                            'can',
                        ],
                    ],
                ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_protected_routes()
    {
        $response = $this->getJson('/api/auth/account');

        $response->assertStatus(401);
    }

    /** @test */
    public function user_can_logout()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('Password0!'),
            'is_active' => true,
        ]);
        $user->assignRole('employee');

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/auth/logout');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Logout successful',
                ]);

        // Verify token was deleted
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);
    }
}
