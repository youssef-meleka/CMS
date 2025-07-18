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
        return User::all();
    }

    /**
     * Find user by ID
     */
    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Create new user
     */
    public function create(array $data): User
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return User::create($data);
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
        return User::where('role', $role)->get();
    }

    /**
     * Get active users
     */
    public function getActiveUsers(): Collection
    {
        return User::where('is_active', true)->get();
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
}
