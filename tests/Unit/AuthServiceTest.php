<?php

namespace Tests\Unit;

use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles and permissions
        $this->setupRolesAndPermissions();

        $this->authService = new AuthService(new UserRepository(new User()));
    }

    private function setupRolesAndPermissions(): void
    {
        // Create permissions
        Permission::create(['name' => 'manage users']);
        Permission::create(['name' => 'manage products']);
        Permission::create(['name' => 'manage orders']);
        Permission::create(['name' => 'access dashboard']);
        Permission::create(['name' => 'view products']);
        Permission::create(['name' => 'view orders']);
        Permission::create(['name' => 'view own orders']);

        // Create roles
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo(['manage users', 'manage products', 'manage orders', 'access dashboard']);

        $manager = Role::create(['name' => 'manager']);
        $manager->givePermissionTo(['manage products', 'manage orders', 'access dashboard']);

        $employee = Role::create(['name' => 'employee']);
        $employee->givePermissionTo(['view products', 'view orders', 'view own orders']);

        $customer = Role::create(['name' => 'customer']);
        $customer->givePermissionTo(['view products', 'view own orders']);
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
        $this->assertTrue($user->hasRole('employee'));
    }

    /** @test */
    public function can_login_user()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('Password0!'),
        ]);
        $user->assignRole('employee');

        $credentials = [
            'email' => 'test@example.com',
            'password' => 'Password0!',
        ];

        $result = $this->authService->login($credentials);

        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('token', $result);
        $this->assertInstanceOf(User::class, $result['user']);
        $this->assertIsString($result['token']);
    }

    /** @test */
    public function cannot_login_with_wrong_credentials()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('Password0!'),
        ]);
        $user->assignRole('employee');

        $credentials = [
            'email' => 'test@example.com',
            'password' => 'WrongPassword',
        ];

        $this->expectException(ValidationException::class);
        $this->authService->login($credentials);
    }

    /** @test */
    public function cannot_login_inactive_user()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('Password0!'),
            'is_active' => false,
        ]);
        $user->assignRole('employee');

        $credentials = [
            'email' => 'test@example.com',
            'password' => 'Password0!',
        ];

        $this->expectException(ValidationException::class);
        $this->authService->login($credentials);
    }

    /** @test */
    public function can_check_user_permissions()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('Password0!'),
        ]);
        $admin->assignRole('admin');

        $manager = User::create([
            'name' => 'Manager User',
            'email' => 'manager@example.com',
            'password' => Hash::make('Password0!'),
        ]);
        $manager->assignRole('manager');

        $employee = User::create([
            'name' => 'Employee User',
            'email' => 'employee@example.com',
            'password' => Hash::make('Password0!'),
        ]);
        $employee->assignRole('employee');

        // Test admin permissions
        $this->assertTrue($this->authService->hasPermission($admin, 'manage products'));
        $this->assertTrue($this->authService->hasPermission($admin, 'manage orders'));
        $this->assertTrue($this->authService->hasPermission($admin, 'manage users'));

        // Test manager permissions
        $this->assertTrue($this->authService->hasPermission($manager, 'manage products'));
        $this->assertTrue($this->authService->hasPermission($manager, 'manage orders'));
        $this->assertFalse($this->authService->hasPermission($manager, 'manage users'));

        // Test employee permissions
        $this->assertFalse($this->authService->hasPermission($employee, 'manage products'));
        $this->assertFalse($this->authService->hasPermission($employee, 'manage orders'));
        $this->assertFalse($this->authService->hasPermission($employee, 'manage users'));
    }

    /** @test */
    public function can_check_resource_access()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('Password0!'),
        ]);
        $admin->assignRole('admin');

        $employee = User::create([
            'name' => 'Employee User',
            'email' => 'employee@example.com',
            'password' => Hash::make('Password0!'),
        ]);
        $employee->assignRole('employee');

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
        ]);
        $user->assignRole('employee');

        // Simulate authenticated user
        auth()->login($user);

        $authenticatedUser = $this->authService->getAuthenticatedUser();

        $this->assertInstanceOf(User::class, $authenticatedUser);
        $this->assertEquals($user->id, $authenticatedUser->id);
    }

    /** @test */
    public function can_assign_role_to_user()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('Password0!'),
        ]);

        $result = $this->authService->assignRole($user, 'manager');

        $this->assertTrue($result);
        $this->assertTrue($user->hasRole('manager'));
    }

    /** @test */
    public function can_give_permission_to_user()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('Password0!'),
        ]);

        $result = $this->authService->givePermission($user, 'manage products');

        $this->assertTrue($result);
        $this->assertTrue($user->hasPermissionTo('manage products'));
    }
}
