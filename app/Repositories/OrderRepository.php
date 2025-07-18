<?php

namespace App\Repositories;

use App\Models\Order;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderRepository implements OrderRepositoryInterface
{
    /**
     * Get all orders
     */
    public function all(): Collection
    {
        return Order::with(['customer', 'assignedUser', 'orderItems.product'])->get();
    }

    /**
     * Get paginated orders
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Order::with(['customer', 'assignedUser', 'orderItems.product'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Find order by ID
     */
    public function findById(int $id): ?Order
    {
        return Order::with(['customer', 'assignedUser', 'orderItems.product'])->find($id);
    }

    /**
     * Find order by order number
     */
    public function findByOrderNumber(string $orderNumber): ?Order
    {
        return Order::where('order_number', $orderNumber)
            ->with(['customer', 'assignedUser', 'orderItems.product'])
            ->first();
    }

    /**
     * Create new order
     */
    public function create(array $data): Order
    {
        return Order::create($data);
    }

    /**
     * Update order
     */
    public function update(int $id, array $data): bool
    {
        $order = Order::find($id);

        if (!$order) {
            return false;
        }

        return $order->update($data);
    }

    /**
     * Delete order
     */
    public function delete(int $id): bool
    {
        $order = Order::find($id);

        if (!$order) {
            return false;
        }

        return $order->delete();
    }

    /**
     * Get orders by status
     */
    public function getByStatus(string $status): Collection
    {
        return Order::byStatus($status)
            ->with(['customer', 'assignedUser'])
            ->get();
    }

    /**
     * Get orders by customer
     */
    public function getByCustomer(int $customerId): Collection
    {
        return Order::where('customer_id', $customerId)
            ->with(['assignedUser', 'orderItems.product'])
            ->get();
    }

    /**
     * Get orders assigned to user
     */
    public function getAssignedToUser(int $userId): Collection
    {
        return Order::assignedTo($userId)
            ->with(['customer', 'orderItems.product'])
            ->get();
    }

    /**
     * Update order status
     */
    public function updateStatus(int $id, string $status): bool
    {
        $order = Order::find($id);

        if (!$order) {
            return false;
        }

        $order->status = $status;

        if ($status === 'shipped') {
            $order->shipped_at = now();
        } elseif ($status === 'delivered') {
            $order->delivered_at = now();
        }

        return $order->save();
    }

    /**
     * Assign order to user
     */
    public function assignToUser(int $orderId, int $userId): bool
    {
        $order = Order::find($orderId);

        if (!$order) {
            return false;
        }

        $order->assigned_to = $userId;
        return $order->save();
    }

    /**
     * Get orders within date range
     */
    public function getOrdersByDateRange(string $startDate, string $endDate): Collection
    {
        return Order::whereBetween('created_at', [$startDate, $endDate])
            ->with(['customer', 'assignedUser'])
            ->get();
    }
}
