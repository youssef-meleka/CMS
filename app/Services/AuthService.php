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

    /**
     * Register a new user
     */
    public function register(array $data): User
    {
        return $this->userRepository->create($data);
    }

    /**
     * Authenticate user and return token
     */
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

    /**
     * Logout user
     */
    public function logout(): bool
    {
        $user = Auth::user();

        if (!$user) {
            throw new AuthenticationException('User not authenticated');
        }

        $user->currentAccessToken()->delete();
        return true;
    }

    /**
     * Get authenticated user
     */
    public function getAuthenticatedUser(): ?User
    {
        return Auth::user();
    }

    /**
     * Check if user has permission
     */
    public function hasPermission(User $user, string $permission): bool
    {
        return match ($permission) {
            'manage_products' => $user->canManageProducts(),
            'manage_orders' => $user->canManageOrders(),
            'manage_users' => $user->isAdmin(),
            'view_dashboard' => true,
            default => false,
        };
    }

    /**
     * Check if user can access resource
     */
    public function canAccessResource(User $user, string $resource, ?int $resourceId = null): bool
    {
        return match ($resource) {
            'products' => $user->canManageProducts(),
            'orders' => $user->canManageOrders(),
            'users' => $user->isAdmin(),
            'own_orders' => $resourceId ? $this->canAccessOwnOrder($user, $resourceId) : false,
            default => false,
        };
    }

    /**
     * Check if user can access their own order
     */
    private function canAccessOwnOrder(User $user, int $orderId): bool
    {
        return $user->orders()->where('id', $orderId)->exists() ||
               $user->assignedOrders()->where('id', $orderId)->exists();
    }
}
