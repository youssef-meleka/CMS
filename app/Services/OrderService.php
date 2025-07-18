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

    /**
     * Get all orders
     */
    public function getAllOrders(): Collection
    {
        return $this->orderRepository->all();
    }

    /**
     * Get paginated orders
     */
    public function getPaginatedOrders(int $perPage = 15): LengthAwarePaginator
    {
        return $this->orderRepository->paginate($perPage);
    }

    /**
     * Get order by ID
     */
    public function getOrderById(int $id): ?Order
    {
        return $this->orderRepository->findById($id);
    }

    /**
     * Create new order
     */
    public function createOrder(array $orderData, array $items, User $customer): Order
    {
        return DB::transaction(function () use ($orderData, $items, $customer) {
            // Generate order number
            $orderNumber = Order::generateOrderNumber();

            // Calculate total amount
            $totalAmount = $this->calculateTotalAmount($items);

            // Create order
            $order = $this->orderRepository->create([
                'order_number' => $orderNumber,
                'customer_id' => $customer->id,
                'total_amount' => $totalAmount,
                'status' => $orderData['status'] ?? 'pending',
                'shipping_address' => $orderData['shipping_address'],
                'billing_address' => $orderData['billing_address'],
                'notes' => $orderData['notes'] ?? null,
            ]);

            // Create order items and update stock
            foreach ($items as $item) {
                $product = $this->productRepository->findById($item['product_id']);

                if (!$product) {
                    throw new \Exception("Product not found: {$item['product_id']}");
                }

                if (!$this->productService->isProductAvailable($item['product_id'], $item['quantity'])) {
                    throw new \Exception("Insufficient stock for product: {$product->name}");
                }

                // Create order item
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'] ?? $product->price,
                    'total_price' => $item['quantity'] * ($item['unit_price'] ?? $product->price),
                ]);

                // Decrease stock
                $this->productService->decreaseStock($item['product_id'], $item['quantity']);
            }

            return $order;
        });
    }

    /**
     * Update order
     */
    public function updateOrder(int $id, array $data): bool
    {
        return $this->orderRepository->update($id, $data);
    }

    /**
     * Delete order
     */
    public function deleteOrder(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $order = $this->orderRepository->findById($id);

            if (!$order) {
                return false;
            }

            // Restore stock for each item
            foreach ($order->orderItems as $item) {
                $this->productService->increaseStock($item->product_id, $item->quantity);
            }

            return $this->orderRepository->delete($id);
        });
    }

    /**
     * Update order status
     */
    public function updateOrderStatus(int $id, string $status): bool
    {
        return $this->orderRepository->updateStatus($id, $status);
    }

    /**
     * Assign order to user
     */
    public function assignOrderToUser(int $orderId, int $userId): bool
    {
        return $this->orderRepository->assignToUser($orderId, $userId);
    }

    /**
     * Get orders by status
     */
    public function getOrdersByStatus(string $status): Collection
    {
        return $this->orderRepository->getByStatus($status);
    }

    /**
     * Get orders by customer
     */
    public function getOrdersByCustomer(int $customerId): Collection
    {
        return $this->orderRepository->getByCustomer($customerId);
    }

    /**
     * Get orders assigned to user
     */
    public function getOrdersAssignedToUser(int $userId): Collection
    {
        return $this->orderRepository->getAssignedToUser($userId);
    }

    /**
     * Get orders within date range
     */
    public function getOrdersByDateRange(string $startDate, string $endDate): Collection
    {
        return $this->orderRepository->getOrdersByDateRange($startDate, $endDate);
    }

    /**
     * Calculate total amount from items
     */
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

    /**
     * Get order statistics
     */
    public function getOrderStatistics(): array
    {
        $orders = $this->orderRepository->all();

        return [
            'total_orders' => $orders->count(),
            'pending_orders' => $orders->where('status', 'pending')->count(),
            'processing_orders' => $orders->where('status', 'processing')->count(),
            'shipped_orders' => $orders->where('status', 'shipped')->count(),
            'delivered_orders' => $orders->where('status', 'delivered')->count(),
            'cancelled_orders' => $orders->where('status', 'cancelled')->count(),
            'total_revenue' => round($orders->where('status', '!=', 'cancelled')->sum('total_amount'), 3),
        ];
    }

    /**
     * Get available order statuses
     */
    public function getAvailableStatuses(): array
    {
        return $this->orderRepository->all()->pluck('status')->unique()->values()->toArray();
    }
}
