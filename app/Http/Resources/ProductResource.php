<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'category' => $this->category,
            'stock_quantity' => $this->stock_quantity,
            'sku' => $this->sku,
            'image_url' => $this->image_url,
            'is_active' => $this->is_active,
            'created_by' => $this->created_by,
            'creator' => new UserResource($this->whenLoaded('creator')),

            // Stock status information
            'stock_status' => [
                'is_in_stock' => $this->isInStock(),
                'is_low_stock' => $this->stock_quantity <= 10,
                'status_label' => $this->getStockStatusLabel(),
            ],

            // Order statistics
            'order_statistics' => [
                'total_orders' => $this->whenLoaded('orderItems', function () {
                    return $this->orderItems->count();
                }),
                'total_quantity_sold' => $this->whenLoaded('orderItems', function () {
                    return $this->orderItems->sum('quantity');
                }),
                'total_revenue' => $this->whenLoaded('orderItems', function () {
                    return $this->orderItems->sum('total_price');
                }),
            ],

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Get stock status label
     */
    private function getStockStatusLabel(): string
    {
        if ($this->stock_quantity <= 0) {
            return 'Out of Stock';
        } elseif ($this->stock_quantity <= 10) {
            return 'Low Stock';
        } else {
            return 'In Stock';
        }
    }
}
