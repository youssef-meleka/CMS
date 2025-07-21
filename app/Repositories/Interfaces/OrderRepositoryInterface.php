<?php

namespace App\Repositories\Interfaces;

use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface OrderRepositoryInterface extends BaseRepositoryInterface
{

    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function getByStatusPaginated(string $status, int $perPage = 15): LengthAwarePaginator;

    public function getByCustomerPaginated(int $customerId, int $perPage = 15): LengthAwarePaginator;

    public function getAssignedToUserPaginated(int $userId, int $perPage = 15): LengthAwarePaginator;

    public function updateStatus(int $id, string $status): bool;

    public function assignToUser(int $orderId, int $userId): bool;

    public function getOrdersByDateRange(string $startDate, string $endDate): Collection;
}
