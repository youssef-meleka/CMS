<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class ProductService
{
    protected ProductRepositoryInterface $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * Get all products
     */
    public function getAllProducts(): Collection
    {
        return $this->productRepository->all();
    }

    /**
     * Get paginated products
     */
    public function getPaginatedProducts(int $perPage = 15): LengthAwarePaginator
    {
        return $this->productRepository->paginate($perPage);
    }

    /**
     * Get product by ID
     */
    public function getProductById(int $id): ?Product
    {
        return $this->productRepository->findById($id);
    }

    /**
     * Create new product
     */
    public function createProduct(array $data, User $user): Product
    {
        $data['created_by'] = $user->id;
        $data['sku'] = $this->generateSku($data['name']);

        return $this->productRepository->create($data);
    }

    /**
     * Update product
     */
    public function updateProduct(int $id, array $data): bool
    {
        return $this->productRepository->update($id, $data);
    }

    /**
     * Delete product
     */
    public function deleteProduct(int $id): bool
    {
        return $this->productRepository->delete($id);
    }

    /**
     * Search products
     */
    public function searchProducts(string $query): Collection
    {
        return $this->productRepository->search($query);
    }

    /**
     * Get products by category
     */
    public function getProductsByCategory(string $category): Collection
    {
        return $this->productRepository->getByCategory($category);
    }

    /**
     * Get active products
     */
    public function getActiveProducts(): Collection
    {
        return $this->productRepository->getActiveProducts();
    }

    /**
     * Get low stock products
     */
    public function getLowStockProducts(int $threshold = 10): Collection
    {
        return $this->productRepository->getLowStockProducts($threshold);
    }

    /**
     * Update product stock
     */
    public function updateStock(int $id, int $quantity): bool
    {
        return $this->productRepository->updateStock($id, $quantity);
    }

    /**
     * Decrease product stock
     */
    public function decreaseStock(int $id, int $quantity): bool
    {
        $product = $this->productRepository->findById($id);

        if (!$product || $product->stock_quantity < $quantity) {
            return false;
        }

        return $this->productRepository->updateStock($id, $product->stock_quantity - $quantity);
    }

    /**
     * Increase product stock
     */
    public function increaseStock(int $id, int $quantity): bool
    {
        $product = $this->productRepository->findById($id);

        if (!$product) {
            return false;
        }

        return $this->productRepository->updateStock($id, $product->stock_quantity + $quantity);
    }

    /**
     * Generate unique SKU
     */
    private function generateSku(string $productName): string
    {
        $baseSku = strtoupper(Str::slug($productName, ''));
        $baseSku = substr($baseSku, 0, 8);
        $counter = 1;
        $sku = $baseSku;

        while ($this->productRepository->findBySku($sku)) {
            $sku = $baseSku . $counter;
            $counter++;
        }

        return $sku;
    }

    /**
     * Check if product is available
     */
    public function isProductAvailable(int $id, int $quantity = 1): bool
    {
        $product = $this->productRepository->findById($id);

        return $product && $product->is_active && $product->stock_quantity >= $quantity;
    }

    /**
     * Get product categories
     */
    public function getCategories(): array
    {
        return $this->productRepository->all()
            ->pluck('category')
            ->unique()
            ->values()
            ->toArray();
    }
}
