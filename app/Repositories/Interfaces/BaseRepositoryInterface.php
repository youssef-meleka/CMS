<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;

interface BaseRepositoryInterface
{

    public function all(): Collection;

    public function findById(int $id);

    public function create(array $data);

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;
}
