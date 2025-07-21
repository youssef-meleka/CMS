<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class OrderService
{
    protected OrderRepositoryInterface $orderRepository;
    protected ProductRepositoryInterface $productRepository;
    protected ProductService $productService;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        ProductRepositoryInterface $productRepository,
        ProductService $productService
    ) {
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
        $this->productService = $productService;
    }


    public function getPaginatedOrders(int $perPage = 15): LengthAwarePaginator
    {
        return $this->orderRepository->paginate($perPage);
    }


    public function getOrderById(int $id): ?Order
    {
        return $this->orderRepository->findById($id);
    }


    public function createOrder(array $orderData, array $items, User $customer): Order
    {
        return DB::transaction(function () use ($orderData, $items, $customer) {
            $orderNumber = Order::generateOrderNumber();

            $totalAmount = $this->calculateTotalAmount($items);

            $order = $this->orderRepository->create([
                'order_number' => $orderNumber,
                'customer_id' => $customer->id,
                'total_amount' => $totalAmount,
                'status' => $orderData['status'] ?? 'pending',
                'shipping_address' => $orderData['shipping_address'],
                'billing_address' => $orderData['billing_address'],
                'notes' => $orderData['notes'] ?? null,
            ]);

            foreach ($items as $item) {
                $product = $this->productRepository->findById($item['product_id']);

                if (!$product) {
                    throw new \Exception("Product not found: {$item['product_id']}");
                }

                if (!$this->productService->isProductAvailable($item['product_id'], $item['quantity'])) {
                    throw new \Exception("Insufficient stock for product: {$product->name}");
                }

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'] ?? $product->price,
                    'total_price' => $item['quantity'] * ($item['unit_price'] ?? $product->price),
                ]);

                $this->productService->decreaseStock($item['product_id'], $item['quantity']);
            }

            return $order;
        });
    }


    public function updateOrder(int $id, array $data): bool
    {
        return $this->orderRepository->update($id, $data);
    }


    public function deleteOrder(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $order = $this->orderRepository->findById($id);

            if (!$order) {
                return false;
            }

            foreach ($order->orderItems as $item) {
                $this->productService->increaseStock($item->product_id, $item->quantity);
            }

            return $this->orderRepository->delete($id);
        });
    }


    public function updateOrderStatus(int $id, string $status): bool
    {
        return $this->orderRepository->updateStatus($id, $status);
    }


    public function assignOrderToUser(int $orderId, int $userId): bool
    {
        return $this->orderRepository->assignToUser($orderId, $userId);
    }


    public function getOrdersByStatus(string $status, int $perPage = 15): LengthAwarePaginator
    {
        return $this->orderRepository->getByStatusPaginated($status, $perPage);
    }


    public function getOrdersByCustomer(int $customerId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->orderRepository->getByCustomerPaginated($customerId, $perPage);
    }


    public function getOrdersAssignedToUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->orderRepository->getAssignedToUserPaginated($userId, $perPage);
    }


    private function calculateTotalAmount(array $items): float
    {
        $total = 0;

        foreach ($items as $item) {
            $product = $this->productRepository->findById($item['product_id']);
            $unitPrice = $item['unit_price'] ?? $product->price;
            $total += $item['quantity'] * $unitPrice;
        }

        return $total;
    }


    public function getOrderStatistics(): array
    {
        $orders = $this->orderRepository->all();

        return [
            'total_orders' => $orders->count(),
            'total_revenue' => round($orders->where('status', '!=', 'cancelled')->sum('total_amount'), 3),
            'orders_by_status' => [
                'pending' => $orders->where('status', 'pending')->count(),
                'processing' => $orders->where('status', 'processing')->count(),
                'shipped' => $orders->where('status', 'shipped')->count(),
                'delivered' => $orders->where('status', 'delivered')->count(),
                'cancelled' => $orders->where('status', 'cancelled')->count(),
            ],
            'average_order_value' => $orders->where('status', '!=', 'cancelled')->count() > 0
                ? round($orders->where('status', '!=', 'cancelled')->sum('total_amount') / $orders->where('status', '!=', 'cancelled')->count(), 3)
                : 0,
            'recent_orders' => $orders->take(5)->values(),
        ];
    }


    public function getAvailableStatuses(): array
    {
        return $this->orderRepository->all()->pluck('status')->unique()->values()->toArray();
    }
}
