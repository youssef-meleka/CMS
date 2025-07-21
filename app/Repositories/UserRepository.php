<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function all(): Collection
    {
        return User::with('roles')->get();
    }

    public function findById(int $id): ?User
    {
        return User::with('roles')->find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return User::with('roles')->where('email', $email)->first();
    }

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

}
