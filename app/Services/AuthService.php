<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function register(array $data): User
    {
        $user = $this->userRepository->create($data);

        // Assign default role if not specified
        if (!isset($data['role'])) {
            $user->assignRole('employee');
        } else {
            $user->assignRole($data['role']);
        }

        return $user;
    }

    public function login(array $credentials): array
    {
        $user = $this->userRepository->findByEmail($credentials['email']);

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Your account has been deactivated.'],
            ]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function logout(): bool
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user) {
            throw new AuthenticationException('User not authenticated');
        }

        // Delete all tokens for the user
        $user->tokens()->delete();
        return true;
    }

    public function getAuthenticatedUser(): ?User
    {
        return Auth::user();
    }

    public function hasPermission(User $user, string $permission): bool
    {
        return $user->hasPermissionTo($permission);
    }

    public function canAccessResource(User $user, string $resource, ?int $resourceId = null): bool
    {
        return match ($resource) {
            'products' => $user->hasPermissionTo('manage products'),
            'orders' => $user->hasPermissionTo('manage orders'),
            'users' => $user->hasPermissionTo('manage users'),
            'dashboard' => $user->hasPermissionTo('access dashboard'),
            'own_orders' => $resourceId ? $this->canAccessOwnOrder($user, $resourceId) :
                           $user->hasPermissionTo('view own orders'),
            default => false,
        };
    }

    private function canAccessOwnOrder(User $user, int $orderId): bool
    {
        // Check if user has permission to view own orders and the order belongs to them
        if (!$user->hasPermissionTo('view own orders')) {
            return false;
        }

        return $user->orders()->where('id', $orderId)->exists() ||
               $user->assignedOrders()->where('id', $orderId)->exists();
    }

    public function assignRole(User $user, string $role): bool
    {
        try {
            $user->assignRole($role);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function removeRole(User $user, string $role): bool
    {
        try {
            $user->removeRole($role);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function givePermission(User $user, string $permission): bool
    {
        try {
            $user->givePermissionTo($permission);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function revokePermission(User $user, string $permission): bool
    {
        try {
            $user->revokePermissionTo($permission);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
