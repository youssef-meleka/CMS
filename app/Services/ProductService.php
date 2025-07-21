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


    public function getAllProducts(int $perPage = 15): LengthAwarePaginator
    {
        return $this->productRepository->paginate($perPage);
    }



    public function getProductById(int $id): ?Product
    {
        return $this->productRepository->findById($id);
    }


    public function createProduct(array $data, User $user): Product
    {
        $data['created_by'] = $user->id;
        $data['sku'] = $this->generateSku($data['name']);

        return $this->productRepository->create($data);
    }


    public function updateProduct(int $id, array $data): bool
    {
        return $this->productRepository->update($id, $data);
    }


    public function deleteProduct(int $id): array
    {
        $product = $this->productRepository->findById($id);

        if (!$product) {
            return [
                'success' => false,
                'message' => 'Product not found'
            ];
        }

        // Check if product has related order items
        if ($product->orderItems()->count() > 0) {
            return [
                'success' => false,
                'message' => 'Cannot delete product because it has related orders. Please remove all related orders first.'
            ];
        }

        $deleted = $this->productRepository->delete($id);

        if ($deleted) {
            return [
                'success' => true,
                'message' => 'Product deleted successfully'
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to delete product'
        ];
    }


    public function searchProducts(string $query, int $perPage = 15): LengthAwarePaginator
    {
        return $this->productRepository->searchPaginated($query, $perPage);
    }


    public function getProductsByCategory(string $category, int $perPage = 15): LengthAwarePaginator
    {
        return $this->productRepository->getByCategoryPaginated($category, $perPage);
    }


    public function getActiveProducts(): Collection
    {
        return $this->productRepository->getActiveProducts();
    }


    public function getLowStockProducts(int $threshold = 10): Collection
    {
        return $this->productRepository->getLowStockProducts($threshold);
    }


    public function updateStock(int $id, int $quantity): bool
    {
        return $this->productRepository->updateStock($id, $quantity);
    }


    public function decreaseStock(int $id, int $quantity): bool
    {
        $product = $this->productRepository->findById($id);

        if (!$product || $product->stock_quantity < $quantity) {
            return false;
        }

        return $this->productRepository->updateStock($id, $product->stock_quantity - $quantity);
    }


    public function increaseStock(int $id, int $quantity): bool
    {
        $product = $this->productRepository->findById($id);

        if (!$product) {
            return false;
        }

        return $this->productRepository->updateStock($id, $product->stock_quantity + $quantity);
    }


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


    public function isProductAvailable(int $id, int $quantity = 1): bool
    {
        $product = $this->productRepository->findById($id);

        return $product && $product->is_active && $product->stock_quantity >= $quantity;
    }


    public function getCategories(): array
    {
        $categories = $this->productRepository->all()
            ->pluck('category')
            ->unique()
            ->values()
            ->toArray();

        $result = [];
        foreach ($categories as $category) {
            $count = $this->productRepository->all()
                ->where('category', $category)
                ->count();
            $result[] = [
                'category' => $category,
                'count' => $count,
            ];
        }

        return $result;
    }
}
