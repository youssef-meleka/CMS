<?php

namespace App\Repositories\Interfaces;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface
{
    /**
     * Get all users
     */
    public function all(): Collection;

    /**
     * Find user by ID
     */
    public function findById(int $id): ?User;

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User;

    /**
     * Create new user
     */
    public function create(array $data): User;

    /**
     * Update user
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete user
     */
    public function delete(int $id): bool;

    /**
     * Get users by role
     */
    public function getByRole(string $role): Collection;

    /**
     * Get active users
     */
    public function getActiveUsers(): Collection;

    /**
     * Activate/deactivate user
     */
    public function toggleStatus(int $id): bool;
}
