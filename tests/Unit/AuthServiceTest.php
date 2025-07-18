<?php

namespace Tests\Unit;

use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AuthService $authService;
    protected UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = new UserRepository();
        $this->authService = new AuthService($this->userRepository);
    }

    /** @test */
    public function can_register_user()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password0!',
            'role' => 'employee',
        ];

        $user = $this->authService->register($userData);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertEquals('employee', $user->role);
        $this->assertTrue(Hash::check('Password0!', $user->password));
    }

    /** @test */
    public function can_login_user_with_valid_credentials()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('Password0!'),
            'role' => 'employee',
            'is_active' => true,
        ]);

        $credentials = [
            'email' => 'test@example.com',
            'password' => 'Password0!',
        ];

        $result = $this->authService->login($credentials);

        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('token', $result);
        $this->assertEquals($user->id, $result['user']->id);
        $this->assertNotEmpty($result['token']);
    }

    /** @test */
    public function login_fails_with_invalid_credentials()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('Password0!'),
            'role' => 'employee',
            'is_active' => true,
        ]);

        $credentials = [
            'email' => 'test@example.com',
            'password' => 'WrongPassword',
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->authService->login($credentials);
    }

    /** @test */
    public function login_fails_with_inactive_user()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('Password0!'),
            'role' => 'employee',
            'is_active' => false,
        ]);

        $credentials = [
            'email' => 'test@example.com',
            'password' => 'Password0!',
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->authService->login($credentials);
    }

    /** @test */
    public function can_check_user_permissions()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('Password0!'),
            'role' => 'admin',
        ]);

        $manager = User::create([
            'name' => 'Manager User',
            'email' => 'manager@example.com',
            'password' => Hash::make('Password0!'),
            'role' => 'manager',
        ]);

        $employee = User::create([
            'name' => 'Employee User',
            'email' => 'employee@example.com',
            'password' => Hash::make('Password0!'),
            'role' => 'employee',
        ]);

        // Test admin permissions
        $this->assertTrue($this->authService->hasPermission($admin, 'manage_products'));
        $this->assertTrue($this->authService->hasPermission($admin, 'manage_orders'));
        $this->assertTrue($this->authService->hasPermission($admin, 'manage_users'));

        // Test manager permissions
        $this->assertTrue($this->authService->hasPermission($manager, 'manage_products'));
        $this->assertTrue($this->authService->hasPermission($manager, 'manage_orders'));
        $this->assertFalse($this->authService->hasPermission($manager, 'manage_users'));

        // Test employee permissions
        $this->assertFalse($this->authService->hasPermission($employee, 'manage_products'));
        $this->assertFalse($this->authService->hasPermission($employee, 'manage_orders'));
        $this->assertFalse($this->authService->hasPermission($employee, 'manage_users'));
    }

    /** @test */
    public function can_check_resource_access()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('Password0!'),
            'role' => 'admin',
        ]);

        $employee = User::create([
            'name' => 'Employee User',
            'email' => 'employee@example.com',
            'password' => Hash::make('Password0!'),
            'role' => 'employee',
        ]);

        // Test admin resource access
        $this->assertTrue($this->authService->canAccessResource($admin, 'products'));
        $this->assertTrue($this->authService->canAccessResource($admin, 'orders'));
        $this->assertTrue($this->authService->canAccessResource($admin, 'users'));

        // Test employee resource access
        $this->assertFalse($this->authService->canAccessResource($employee, 'products'));
        $this->assertFalse($this->authService->canAccessResource($employee, 'orders'));
        $this->assertFalse($this->authService->canAccessResource($employee, 'users'));
    }

    /** @test */
    public function can_get_authenticated_user()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('Password0!'),
            'role' => 'employee',
        ]);

        // Simulate authenticated user
        auth()->login($user);

        $authenticatedUser = $this->authService->getAuthenticatedUser();

        $this->assertInstanceOf(User::class, $authenticatedUser);
        $this->assertEquals($user->id, $authenticatedUser->id);
    }
}
