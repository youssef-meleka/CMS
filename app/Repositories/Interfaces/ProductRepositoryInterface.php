<?php

namespace App\Repositories\Interfaces;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface
{
    /**
     * Get all products
     */
    public function all(): Collection;

    /**
     * Get paginated products
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * Find product by ID
     */
    public function findById(int $id): ?Product;

    /**
     * Find product by SKU
     */
    public function findBySku(string $sku): ?Product;

    /**
     * Create new product
     */
    public function create(array $data): Product;

    /**
     * Update product
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete product
     */
    public function delete(int $id): bool;

    /**
     * Search products
     */
    public function search(string $query): Collection;

    /**
     * Search products with pagination
     */
    public function searchPaginated(string $query, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get products by category
     */
    public function getByCategory(string $category): Collection;

    /**
     * Get products by category with pagination
     */
    public function getByCategoryPaginated(string $category, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get active products
     */
    public function getActiveProducts(): Collection;

    /**
     * Get low stock products
     */
    public function getLowStockProducts(int $threshold = 10): Collection;

    /**
     * Update stock quantity
     */
    public function updateStock(int $id, int $quantity): bool;
}
