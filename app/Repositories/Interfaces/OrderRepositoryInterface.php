<?php

namespace App\Repositories\Interfaces;

use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface OrderRepositoryInterface
{
    /**
     * Get all orders
     */
    public function all(): Collection;

    /**
     * Get paginated orders
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * Find order by ID
     */
    public function findById(int $id): ?Order;

    /**
     * Find order by order number
     */
    public function findByOrderNumber(string $orderNumber): ?Order;

    /**
     * Create new order
     */
    public function create(array $data): Order;

    /**
     * Update order
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete order
     */
    public function delete(int $id): bool;

    /**
     * Get orders by status
     */
    public function getByStatus(string $status): Collection;

    /**
     * Get orders by customer
     */
    public function getByCustomer(int $customerId): Collection;

    /**
     * Get orders assigned to user
     */
    public function getAssignedToUser(int $userId): Collection;

    /**
     * Update order status
     */
    public function updateStatus(int $id, string $status): bool;

    /**
     * Assign order to user
     */
    public function assignToUser(int $orderId, int $userId): bool;

    /**
     * Get orders within date range
     */
    public function getOrdersByDateRange(string $startDate, string $endDate): Collection;
}
