<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'order_number' => $this->order_number,
            'customer_id' => $this->customer_id,
            'total_amount' => $this->total_amount,
            'status' => $this->status,
            'shipping_address' => $this->shipping_address,
            'billing_address' => $this->billing_address,
            'notes' => $this->notes,
            'assigned_to' => $this->assigned_to,
            'shipped_at' => $this->shipped_at,
            'delivered_at' => $this->delivered_at,

            // Related models
            'customer' => new UserResource($this->whenLoaded('customer')),
            'assigned_user' => new UserResource($this->whenLoaded('assignedUser')),
            'order_items' => OrderItemResource::collection($this->whenLoaded('orderItems')),

            // Status information
            'status_info' => [
                'is_pending' => $this->isPending(),
                'is_shipped' => $this->isShipped(),
                'is_delivered' => $this->isDelivered(),
                'can_be_cancelled' => $this->isPending(),
                'status_label' => $this->getStatusLabel(),
            ],

            // Order statistics
            'statistics' => [
                'items_count' => $this->whenLoaded('orderItems', function () {
                    return $this->orderItems->count();
                }),
                'total_quantity' => $this->whenLoaded('orderItems', function () {
                    return $this->orderItems->sum('quantity');
                }),
                'calculated_total' => $this->whenLoaded('orderItems', function () {
                    return $this->orderItems->sum('total_price');
                }),
            ],

            // Timeline
            'timeline' => [
                'created_at' => $this->created_at,
                'shipped_at' => $this->shipped_at,
                'delivered_at' => $this->delivered_at,
                'processing_time' => $this->getProcessingTime(),
            ],

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Get status label
     */
    private function getStatusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Pending',
            'processing' => 'Processing',
            'shipped' => 'Shipped',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
            default => 'Unknown',
        };
    }

    /**
     * Get processing time in days
     */
    private function getProcessingTime(): ?int
    {
        if ($this->shipped_at) {
            return $this->created_at->diffInDays($this->shipped_at);
        }

        if ($this->delivered_at) {
            return $this->created_at->diffInDays($this->delivered_at);
        }

        return null;
    }
}
