<?php

namespace App\Repositories;

use App\Models\Product;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductRepository implements ProductRepositoryInterface
{
    /**
     * Get all products
     */
    public function all(): Collection
    {
        return Product::with('creator')->get();
    }

    /**
     * Get paginated products
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Product::with('creator')->paginate($perPage);
    }

    /**
     * Find product by ID
     */
    public function findById(int $id): ?Product
    {
        return Product::with('creator')->find($id);
    }

    /**
     * Find product by SKU
     */
    public function findBySku(string $sku): ?Product
    {
        return Product::where('sku', $sku)->first();
    }

    /**
     * Create new product
     */
    public function create(array $data): Product
    {
        return Product::create($data);
    }

    /**
     * Update product
     */
    public function update(int $id, array $data): bool
    {
        $product = Product::find($id);

        if (!$product) {
            return false;
        }

        return $product->update($data);
    }

    /**
     * Delete product
     */
    public function delete(int $id): bool
    {
        $product = Product::find($id);

        if (!$product) {
            return false;
        }

        return $product->delete();
    }

    /**
     * Get products by category
     */
    public function getByCategory(string $category): Collection
    {
        return Product::byCategory($category)->active()->get();
    }

    /**
     * Get active products
     */
    public function getActiveProducts(): Collection
    {
        return Product::active()->get();
    }

    /**
     * Search products
     */
    public function search(string $query): Collection
    {
        return Product::where('name', 'LIKE', "%{$query}%")
            ->orWhere('description', 'LIKE', "%{$query}%")
            ->orWhere('category', 'LIKE', "%{$query}%")
            ->active()
            ->get();
    }

    /**
     * Search products with pagination
     */
    public function searchPaginated(string $query, int $perPage = 15): LengthAwarePaginator
    {
        return Product::where('name', 'LIKE', "%{$query}%")
            ->orWhere('description', 'LIKE', "%{$query}%")
            ->orWhere('category', 'LIKE', "%{$query}%")
            ->active()
            ->paginate($perPage);
    }

    /**
     * Get products by category with pagination
     */
    public function getByCategoryPaginated(string $category, int $perPage = 15): LengthAwarePaginator
    {
        return Product::byCategory($category)->active()->paginate($perPage);
    }

    /**
     * Get low stock products
     */
    public function getLowStockProducts(int $threshold = 10): Collection
    {
        return Product::where('stock_quantity', '<=', $threshold)
            ->active()
            ->get();
    }

    /**
     * Update stock quantity
     */
    public function updateStock(int $id, int $quantity): bool
    {
        $product = Product::find($id);

        if (!$product) {
            return false;
        }

        $product->stock_quantity = $quantity;
        return $product->save();
    }
}
