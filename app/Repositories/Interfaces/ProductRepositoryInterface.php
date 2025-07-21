<?php

namespace App\Repositories\Interfaces;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface extends BaseRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function findBySku(string $sku): ?Product;

    public function searchPaginated(string $query, int $perPage = 15): LengthAwarePaginator;

    public function getByCategoryPaginated(string $category, int $perPage = 15): LengthAwarePaginator;

    public function getActiveProducts(): Collection;

    public function getLowStockProducts(int $threshold = 10): Collection;

    public function updateStock(int $id, int $quantity): bool;
}
