<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

class UserRepository implements UserRepositoryInterface
{
    /**
     * Get all users
     */
    public function all(): Collection
    {
        return User::with('roles')->get();
    }

    /**
     * Find user by ID
     */
    public function findById(int $id): ?User
    {
        return User::with('roles')->find($id);
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User
    {
        return User::with('roles')->where('email', $email)->first();
    }

    /**
     * Create new user
     */
    public function create(array $data): User
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        // Remove role from data as it will be handled by Spatie
        $role = $data['role'] ?? 'employee';
        unset($data['role']);

        $user = User::create($data);
        $user->assignRole($role);

        return $user->load('roles');
    }

    /**
     * Update user
     */
    public function update(int $id, array $data): bool
    {
        $user = User::find($id);

        if (!$user) {
            return false;
        }

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        // Handle role update with Spatie
        if (isset($data['role'])) {
            $role = $data['role'];
            unset($data['role']);
            $user->syncRoles([$role]);
        }

        return $user->update($data);
    }

    /**
     * Delete user
     */
    public function delete(int $id): bool
    {
        $user = User::find($id);

        if (!$user) {
            return false;
        }

        return $user->delete();
    }

    /**
     * Get users by role
     */
    public function getByRole(string $role): Collection
    {
        return User::role($role)->get();
    }

    /**
     * Get active users
     */
    public function getActiveUsers(): Collection
    {
        return User::where('is_active', true)->with('roles')->get();
    }

    /**
     * Activate/deactivate user
     */
    public function toggleStatus(int $id): bool
    {
        $user = User::find($id);

        if (!$user) {
            return false;
        }

        $user->is_active = !$user->is_active;
        return $user->save();
    }

    /**
     * Get users with specific permissions
     */
    public function getUsersWithPermission(string $permission): Collection
    {
        return User::permission($permission)->get();
    }

    /**
     * Get users with dashboard access
     */
    public function getDashboardUsers(): Collection
    {
        return User::permission('access dashboard')->get();
    }
}
