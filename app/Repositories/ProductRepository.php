<?php

namespace App\Repositories;

use App\Models\Product;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }


    public function all(): Collection
    {
        return Product::with('creator')->get();
    }


    public function findById(int $id): ?Product
    {
        return Product::with('creator')->find($id);
    }


    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Product::with('creator')->paginate($perPage);
    }


    public function findBySku(string $sku): ?Product
    {
        return Product::where('sku', $sku)->first();
    }


    public function getActiveProducts(): Collection
    {
        return Product::active()->get();
    }


    public function searchPaginated(string $query, int $perPage = 15): LengthAwarePaginator
    {
        return Product::where('name', 'LIKE', "%{$query}%")
            ->orWhere('description', 'LIKE', "%{$query}%")
            ->orWhere('category', 'LIKE', "%{$query}%")
            ->active()
            ->paginate($perPage);
    }


    public function getByCategoryPaginated(string $category, int $perPage = 15): LengthAwarePaginator
    {
        return Product::byCategory($category)->active()->paginate($perPage);
    }


    public function getLowStockProducts(int $threshold = 10): Collection
    {
        return Product::where('stock_quantity', '<=', $threshold)
            ->active()
            ->get();
    }

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
