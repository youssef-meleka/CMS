<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Services\OrderService;
use App\Services\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    protected OrderService $orderService;
    protected OrderRepository $orderRepository;
    protected ProductRepository $productRepository;
    protected ProductService $productService;
    protected User $customer;
    protected Product $product1;
    protected Product $product2;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles and permissions
        $this->setupRolesAndPermissions();

        $this->orderRepository = new OrderRepository(new Order());
        $this->productRepository = new ProductRepository(new Product());
        $this->productService = new ProductService($this->productRepository);
        $this->orderService = new OrderService(
            $this->orderRepository,
            $this->productRepository,
            $this->productService
        );

        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => Hash::make('Password0!'),
        ]);
        $admin->assignRole('admin');

        $this->customer = User::create([
            'name' => 'Customer User',
            'email' => 'customer@test.com',
            'password' => Hash::make('Password0!'),
        ]);
        $this->customer->assignRole('customer');

        $this->product1 = Product::create([
            'name' => 'Test Product 1',
            'description' => 'Test Description 1',
            'price' => 99.99,
            'category' => 'Electronics',
            'stock_quantity' => 50,
            'sku' => 'SKU001',
            'created_by' => $admin->id,
        ]);

        $this->product2 = Product::create([
            'name' => 'Test Product 2',
            'description' => 'Test Description 2',
            'price' => 149.99,
            'category' => 'Furniture',
            'stock_quantity' => 25,
            'sku' => 'SKU002',
            'created_by' => $admin->id,
        ]);
    }

    private function setupRolesAndPermissions(): void
    {
        // Create permissions
        Permission::create(['name' => 'manage orders']);
        Permission::create(['name' => 'view orders']);
        Permission::create(['name' => 'view own orders']);
        Permission::create(['name' => 'manage products']);

        // Create roles
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo(['manage orders', 'view orders', 'manage products']);

        $customer = Role::create(['name' => 'customer']);
        $customer->givePermissionTo(['view own orders']);
    }

    /** @test */
    public function can_create_order()
    {
        $orderData = [
            'shipping_address' => '123 Main St, City, State 12345',
            'billing_address' => '123 Main St, City, State 12345',
            'notes' => 'Test order notes',
        ];

        $items = [
            [
                'product_id' => $this->product1->id,
                'quantity' => 2,
                'unit_price' => $this->product1->price,
            ],
            [
                'product_id' => $this->product2->id,
                'quantity' => 1,
                'unit_price' => $this->product2->price,
            ],
        ];

        $order = $this->orderService->createOrder($orderData, $items, $this->customer);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals($this->customer->id, $order->customer_id);
        $this->assertEquals('pending', $order->status);
        $this->assertEquals(349.97, $order->total_amount); // (99.99 * 2) + 149.99
        $this->assertNotEmpty($order->order_number);
    }

    /** @test */
    public function can_get_order_by_id()
    {
        $order = Order::create([
            'order_number' => 'ORD-TEST001',
            'customer_id' => $this->customer->id,
            'total_amount' => 199.98,
            'status' => 'pending',
            'shipping_address' => '123 Main St',
            'billing_address' => '123 Main St',
        ]);

        $retrievedOrder = $this->orderService->getOrderById($order->id);

        $this->assertInstanceOf(Order::class, $retrievedOrder);
        $this->assertEquals($order->id, $retrievedOrder->id);
        $this->assertEquals('ORD-TEST001', $retrievedOrder->order_number);
    }

    /** @test */
    public function can_update_order()
    {
        $order = Order::create([
            'order_number' => 'ORD-TEST002',
            'customer_id' => $this->customer->id,
            'total_amount' => 99.99,
            'status' => 'pending',
            'shipping_address' => 'Original Address',
            'billing_address' => 'Original Address',
        ]);

        $updateData = [
            'shipping_address' => 'Updated Address',
            'notes' => 'Updated notes',
        ];

        $result = $this->orderService->updateOrder($order->id, $updateData);

        $this->assertTrue($result);

        $updatedOrder = $this->orderService->getOrderById($order->id);
        $this->assertEquals('Updated Address', $updatedOrder->shipping_address);
        $this->assertEquals('Updated notes', $updatedOrder->notes);
    }

    /** @test */
    public function can_delete_order()
    {
        $order = Order::create([
            'order_number' => 'ORD-TEST003',
            'customer_id' => $this->customer->id,
            'total_amount' => 99.99,
            'status' => 'pending',
            'shipping_address' => '123 Main St',
            'billing_address' => '123 Main St',
        ]);

        $result = $this->orderService->deleteOrder($order->id);

        $this->assertTrue($result);

        $deletedOrder = $this->orderService->getOrderById($order->id);
        $this->assertNull($deletedOrder);
    }

    /** @test */
    public function can_update_order_status()
    {
        $order = Order::create([
            'order_number' => 'ORD-TEST004',
            'customer_id' => $this->customer->id,
            'total_amount' => 99.99,
            'status' => 'pending',
            'shipping_address' => '123 Main St',
            'billing_address' => '123 Main St',
        ]);

        $result = $this->orderService->updateOrderStatus($order->id, 'shipped');

        $this->assertTrue($result);

        $updatedOrder = $this->orderService->getOrderById($order->id);
        $this->assertEquals('shipped', $updatedOrder->status);
        $this->assertNotNull($updatedOrder->shipped_at);
    }

    /** @test */
    public function can_assign_order_to_user()
    {
        $manager = User::create([
            'name' => 'Manager User',
            'email' => 'manager@test.com',
            'password' => Hash::make('Password0!'),
            'role' => 'manager',
        ]);

        $order = Order::create([
            'order_number' => 'ORD-TEST005',
            'customer_id' => $this->customer->id,
            'total_amount' => 99.99,
            'status' => 'pending',
            'shipping_address' => '123 Main St',
            'billing_address' => '123 Main St',
        ]);

        $result = $this->orderService->assignOrderToUser($order->id, $manager->id);

        $this->assertTrue($result);

        $updatedOrder = $this->orderService->getOrderById($order->id);
        $this->assertEquals($manager->id, $updatedOrder->assigned_to);
    }

    /** @test */
    public function can_get_orders_by_status()
    {
        Order::create([
            'order_number' => 'ORD-TEST006',
            'customer_id' => $this->customer->id,
            'total_amount' => 99.99,
            'status' => 'pending',
            'shipping_address' => '123 Main St',
            'billing_address' => '123 Main St',
        ]);

        Order::create([
            'order_number' => 'ORD-TEST007',
            'customer_id' => $this->customer->id,
            'total_amount' => 149.99,
            'status' => 'shipped',
            'shipping_address' => '456 Oak Ave',
            'billing_address' => '456 Oak Ave',
        ]);

        $pendingOrders = $this->orderService->getOrdersByStatus('pending');

        $this->assertCount(1, $pendingOrders);
        $this->assertEquals('pending', $pendingOrders->first()->status);
    }

    /** @test */
    public function can_get_orders_by_customer()
    {
        $customer2 = User::create([
            'name' => 'Customer 2',
            'email' => 'customer2@test.com',
            'password' => Hash::make('Password0!'),
            'role' => 'employee',
        ]);

        Order::create([
            'order_number' => 'ORD-TEST008',
            'customer_id' => $this->customer->id,
            'total_amount' => 99.99,
            'status' => 'pending',
            'shipping_address' => '123 Main St',
            'billing_address' => '123 Main St',
        ]);

        Order::create([
            'order_number' => 'ORD-TEST009',
            'customer_id' => $customer2->id,
            'total_amount' => 149.99,
            'status' => 'pending',
            'shipping_address' => '456 Oak Ave',
            'billing_address' => '456 Oak Ave',
        ]);

        $customerOrders = $this->orderService->getOrdersByCustomer($this->customer->id);

        $this->assertCount(1, $customerOrders);
        $this->assertEquals($this->customer->id, $customerOrders->first()->customer_id);
    }

    /** @test */
    public function can_get_orders_assigned_to_user()
    {
        $manager = User::create([
            'name' => 'Manager User',
            'email' => 'manager@test.com',
            'password' => Hash::make('Password0!'),
            'role' => 'manager',
        ]);

        Order::create([
            'order_number' => 'ORD-TEST010',
            'customer_id' => $this->customer->id,
            'total_amount' => 99.99,
            'status' => 'pending',
            'shipping_address' => '123 Main St',
            'billing_address' => '123 Main St',
            'assigned_to' => $manager->id,
        ]);

        Order::create([
            'order_number' => 'ORD-TEST011',
            'customer_id' => $this->customer->id,
            'total_amount' => 149.99,
            'status' => 'pending',
            'shipping_address' => '456 Oak Ave',
            'billing_address' => '456 Oak Ave',
        ]);

        $assignedOrders = $this->orderService->getOrdersAssignedToUser($manager->id);

        $this->assertCount(1, $assignedOrders);
        $this->assertEquals($manager->id, $assignedOrders->first()->assigned_to);
    }

    /** @test */
    public function can_get_order_statistics()
    {
        Order::create([
            'order_number' => 'ORD-TEST012',
            'customer_id' => $this->customer->id,
            'total_amount' => 99.99,
            'status' => 'pending',
            'shipping_address' => '123 Main St',
            'billing_address' => '123 Main St',
        ]);

        Order::create([
            'order_number' => 'ORD-TEST013',
            'customer_id' => $this->customer->id,
            'total_amount' => 149.99,
            'status' => 'shipped',
            'shipping_address' => '456 Oak Ave',
            'billing_address' => '456 Oak Ave',
        ]);

        Order::create([
            'order_number' => 'ORD-TEST014',
            'customer_id' => $this->customer->id,
            'total_amount' => 199.99,
            'status' => 'delivered',
            'shipping_address' => '789 Pine St',
            'billing_address' => '789 Pine St',
        ]);

        $statistics = $this->orderService->getOrderStatistics();

        $this->assertEquals(3, $statistics['total_orders']);
        $this->assertEquals(1, $statistics['orders_by_status']['pending']);
        $this->assertEquals(1, $statistics['orders_by_status']['shipped']);
        $this->assertEquals(1, $statistics['orders_by_status']['delivered']);
        $this->assertEquals(449.97, $statistics['total_revenue']); // 99.99 + 149.99 + 199.99
        $this->assertArrayHasKey('average_order_value', $statistics);
        $this->assertArrayHasKey('recent_orders', $statistics);
    }

    /** @test */
    public function can_get_available_statuses()
    {
        // Create orders with all expected statuses
        Order::create([
            'order_number' => 'ORD-STATUS001',
            'customer_id' => $this->customer->id,
            'total_amount' => 99.99,
            'status' => 'pending',
            'shipping_address' => '123 Main St',
            'billing_address' => '123 Main St',
        ]);

        Order::create([
            'order_number' => 'ORD-STATUS002',
            'customer_id' => $this->customer->id,
            'total_amount' => 149.99,
            'status' => 'processing',
            'shipping_address' => '456 Oak Ave',
            'billing_address' => '456 Oak Ave',
        ]);

        Order::create([
            'order_number' => 'ORD-STATUS003',
            'customer_id' => $this->customer->id,
            'total_amount' => 199.99,
            'status' => 'shipped',
            'shipping_address' => '789 Pine St',
            'billing_address' => '789 Pine St',
        ]);

        Order::create([
            'order_number' => 'ORD-STATUS004',
            'customer_id' => $this->customer->id,
            'total_amount' => 299.99,
            'status' => 'delivered',
            'shipping_address' => '321 Elm St',
            'billing_address' => '321 Elm St',
        ]);

        Order::create([
            'order_number' => 'ORD-STATUS005',
            'customer_id' => $this->customer->id,
            'total_amount' => 399.99,
            'status' => 'cancelled',
            'shipping_address' => '654 Maple St',
            'billing_address' => '654 Maple St',
        ]);

        $statuses = $this->orderService->getAvailableStatuses();

        $this->assertIsArray($statuses);
        $this->assertContains('pending', $statuses);
        $this->assertContains('processing', $statuses);
        $this->assertContains('shipped', $statuses);
        $this->assertContains('delivered', $statuses);
        $this->assertContains('cancelled', $statuses);
    }

    /** @test */
    public function creates_order_with_correct_total_amount()
    {
        $orderData = [
            'shipping_address' => '123 Main St',
            'billing_address' => '123 Main St',
        ];

        $items = [
            [
                'product_id' => $this->product1->id,
                'quantity' => 3,
                'unit_price' => $this->product1->price,
            ],
            [
                'product_id' => $this->product2->id,
                'quantity' => 2,
                'unit_price' => $this->product2->price,
            ],
        ];

        $order = $this->orderService->createOrder($orderData, $items, $this->customer);

        $expectedTotal = (99.99 * 3) + (149.99 * 2); // 299.97 + 299.98 = 599.95
        $this->assertEquals($expectedTotal, $order->total_amount);
    }

    /** @test */
    public function decreases_stock_when_order_created()
    {
        $initialStock1 = $this->product1->stock_quantity;
        $initialStock2 = $this->product2->stock_quantity;

        $orderData = [
            'shipping_address' => '123 Main St',
            'billing_address' => '123 Main St',
        ];

        $items = [
            [
                'product_id' => $this->product1->id,
                'quantity' => 5,
            ],
            [
                'product_id' => $this->product2->id,
                'quantity' => 3,
            ],
        ];

        $this->orderService->createOrder($orderData, $items, $this->customer);

        $updatedProduct1 = $this->productService->getProductById($this->product1->id);
        $updatedProduct2 = $this->productService->getProductById($this->product2->id);

        $this->assertEquals($initialStock1 - 5, $updatedProduct1->stock_quantity);
        $this->assertEquals($initialStock2 - 3, $updatedProduct2->stock_quantity);
    }

    /** @test */
    public function restores_stock_when_order_deleted()
    {
        $initialStock1 = $this->product1->stock_quantity;
        $initialStock2 = $this->product2->stock_quantity;

        $orderData = [
            'shipping_address' => '123 Main St',
            'billing_address' => '123 Main St',
        ];

        $items = [
            [
                'product_id' => $this->product1->id,
                'quantity' => 5,
            ],
            [
                'product_id' => $this->product2->id,
                'quantity' => 3,
            ],
        ];

        $order = $this->orderService->createOrder($orderData, $items, $this->customer);

        // Delete the order
        $this->orderService->deleteOrder($order->id);

        $restoredProduct1 = $this->productService->getProductById($this->product1->id);
        $restoredProduct2 = $this->productService->getProductById($this->product2->id);

        $this->assertEquals($initialStock1, $restoredProduct1->stock_quantity);
        $this->assertEquals($initialStock2, $restoredProduct2->stock_quantity);
    }
}
