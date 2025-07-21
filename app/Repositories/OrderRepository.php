<?php

namespace App\Repositories;

use App\Models\Order;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderRepository extends BaseRepository implements OrderRepositoryInterface
{
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }


    public function all(): Collection
    {
        return Order::with(['customer', 'assignedUser', 'orderItems.product'])->get();
    }


    public function findById(int $id): ?Order
    {
        return Order::with(['customer', 'assignedUser', 'orderItems.product'])->find($id);
    }


    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Order::with(['customer', 'assignedUser', 'orderItems.product'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    
    public function getByStatusPaginated(string $status, int $perPage = 15): LengthAwarePaginator
    {
        return Order::byStatus($status)
            ->with(['customer', 'assignedUser'])
            ->paginate($perPage);
    }


    public function getByCustomerPaginated(int $customerId, int $perPage = 15): LengthAwarePaginator
    {
        return Order::where('customer_id', $customerId)
            ->with(['assignedUser', 'orderItems.product'])
            ->paginate($perPage);
    }

    public function getAssignedToUserPaginated(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return Order::assignedTo($userId)
            ->with(['customer', 'orderItems.product'])
            ->paginate($perPage);
    }


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

    public function assignToUser(int $orderId, int $userId): bool
    {
        $order = Order::find($orderId);

        if (!$order) {
            return false;
        }

        $order->assigned_to = $userId;
        return $order->save();
    }


    public function getOrdersByDateRange(string $startDate, string $endDate): Collection
    {
        return Order::whereBetween('created_at', [$startDate, $endDate])
            ->with(['customer', 'assignedUser'])
            ->get();
    }
}
