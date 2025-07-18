<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $manager;
    protected User $customer;
    protected Product $product1;
    protected Product $product2;
    protected string $adminToken;
    protected string $managerToken;
    protected string $customerToken;

    protected function setUp(): void
    {
        parent::setUp();

        // Create users
        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => Hash::make('Password0!'),
            'role' => 'admin',
        ]);

        $this->manager = User::create([
            'name' => 'Manager User',
            'email' => 'manager@test.com',
            'password' => Hash::make('Password0!'),
            'role' => 'manager',
        ]);

        $this->customer = User::create([
            'name' => 'Customer User',
            'email' => 'customer@test.com',
            'password' => Hash::make('Password0!'),
            'role' => 'employee',
        ]);

        // Create products
        $this->product1 = Product::create([
            'name' => 'Test Product 1',
            'description' => 'Test Description 1',
            'price' => 99.99,
            'category' => 'Electronics',
            'stock_quantity' => 50,
            'sku' => 'SKU001',
            'created_by' => $this->admin->id,
        ]);

        $this->product2 = Product::create([
            'name' => 'Test Product 2',
            'description' => 'Test Description 2',
            'price' => 149.99,
            'category' => 'Furniture',
            'stock_quantity' => 25,
            'sku' => 'SKU002',
            'created_by' => $this->admin->id,
        ]);

        // Get tokens
        $this->adminToken = $this->admin->createToken('test-token')->plainTextToken;
        $this->managerToken = $this->manager->createToken('test-token')->plainTextToken;
        $this->customerToken = $this->customer->createToken('test-token')->plainTextToken;
    }

    /** @test */
    public function admin_can_create_order()
    {
        $orderData = [
            'customer_id' => $this->customer->id,
            'shipping_address' => '123 Main St, City, State 12345',
            'billing_address' => '123 Main St, City, State 12345',
            'notes' => 'Test order notes',
            'items' => [
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
            ],
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/orders', $orderData);

        $response->assertStatus(201)
                ->assertJson([
                    'success' => true,
                    'message' => 'Order created successfully',
                ]);

        $this->assertDatabaseHas('orders', [
            'customer_id' => $this->customer->id,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $this->product1->id,
            'quantity' => 2,
        ]);
    }

    /** @test */
    public function manager_can_create_order()
    {
        $orderData = [
            'customer_id' => $this->customer->id,
            'shipping_address' => '456 Oak Ave, City, State 54321',
            'billing_address' => '456 Oak Ave, City, State 54321',
            'items' => [
                [
                    'product_id' => $this->product1->id,
                    'quantity' => 1,
                ],
            ],
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->managerToken,
        ])->postJson('/api/orders', $orderData);

        $response->assertStatus(201)
                ->assertJson([
                    'success' => true,
                    'message' => 'Order created successfully',
                ]);
    }

    /** @test */
    public function employee_cannot_create_order()
    {
        $orderData = [
            'customer_id' => $this->customer->id,
            'shipping_address' => '789 Pine St, City, State 67890',
            'billing_address' => '789 Pine St, City, State 67890',
            'items' => [
                [
                    'product_id' => $this->product1->id,
                    'quantity' => 1,
                ],
            ],
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->customerToken,
        ])->postJson('/api/orders', $orderData);

        $response->assertStatus(403);
    }

    /** @test */
    public function can_list_orders()
    {
        // Create test orders
        $order1 = Order::create([
            'order_number' => 'ORD-TEST001',
            'customer_id' => $this->customer->id,
            'total_amount' => 199.98,
            'status' => 'pending',
            'shipping_address' => '123 Main St',
            'billing_address' => '123 Main St',
        ]);

        $order2 = Order::create([
            'order_number' => 'ORD-TEST002',
            'customer_id' => $this->customer->id,
            'total_amount' => 149.99,
            'status' => 'processing',
            'shipping_address' => '456 Oak Ave',
            'billing_address' => '456 Oak Ave',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/orders');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                ])
                ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function can_get_single_order()
    {
        $order = Order::create([
            'order_number' => 'ORD-TEST003',
            'customer_id' => $this->customer->id,
            'total_amount' => 99.99,
            'status' => 'pending',
            'shipping_address' => '123 Main St',
            'billing_address' => '123 Main St',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'id' => $order->id,
                        'order_number' => 'ORD-TEST003',
                        'status' => 'pending',
                    ],
                ]);
    }

    /** @test */
    public function can_update_order()
    {
        $order = Order::create([
            'order_number' => 'ORD-TEST004',
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

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->putJson("/api/orders/{$order->id}", $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Order updated successfully',
                ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'shipping_address' => 'Updated Address',
            'notes' => 'Updated notes',
        ]);
    }

    /** @test */
    public function can_delete_order()
    {
        $order = Order::create([
            'order_number' => 'ORD-TEST005',
            'customer_id' => $this->customer->id,
            'total_amount' => 99.99,
            'status' => 'pending',
            'shipping_address' => '123 Main St',
            'billing_address' => '123 Main St',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->deleteJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Order deleted successfully',
                ]);

        $this->assertDatabaseMissing('orders', [
            'id' => $order->id,
        ]);
    }

    /** @test */
    public function can_update_order_status()
    {
        $order = Order::create([
            'order_number' => 'ORD-TEST006',
            'customer_id' => $this->customer->id,
            'total_amount' => 99.99,
            'status' => 'pending',
            'shipping_address' => '123 Main St',
            'billing_address' => '123 Main St',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->patchJson("/api/orders/{$order->id}/status", [
            'status' => 'shipped',
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Order status updated successfully',
                ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'shipped',
        ]);
    }

    /** @test */
    public function can_assign_order_to_user()
    {
        $order = Order::create([
            'order_number' => 'ORD-TEST007',
            'customer_id' => $this->customer->id,
            'total_amount' => 99.99,
            'status' => 'pending',
            'shipping_address' => '123 Main St',
            'billing_address' => '123 Main St',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->patchJson("/api/orders/{$order->id}/assign", [
            'user_id' => $this->manager->id,
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Order assigned successfully',
                ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'assigned_to' => $this->manager->id,
        ]);
    }

    /** @test */
    public function can_filter_orders_by_status()
    {
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
            'customer_id' => $this->customer->id,
            'total_amount' => 149.99,
            'status' => 'shipped',
            'shipping_address' => '456 Oak Ave',
            'billing_address' => '456 Oak Ave',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/orders?status=pending');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                ])
                ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function can_filter_orders_by_customer()
    {
        $customer2 = User::create([
            'name' => 'Customer 2',
            'email' => 'customer2@test.com',
            'password' => Hash::make('Password0!'),
            'role' => 'employee',
        ]);

        Order::create([
            'order_number' => 'ORD-TEST010',
            'customer_id' => $this->customer->id,
            'total_amount' => 99.99,
            'status' => 'pending',
            'shipping_address' => '123 Main St',
            'billing_address' => '123 Main St',
        ]);

        Order::create([
            'order_number' => 'ORD-TEST011',
            'customer_id' => $customer2->id,
            'total_amount' => 149.99,
            'status' => 'pending',
            'shipping_address' => '456 Oak Ave',
            'billing_address' => '456 Oak Ave',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson("/api/orders?customer_id={$this->customer->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                ])
                ->assertJsonCount(1, 'data');
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

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/orders/statistics');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'total_orders' => 3,
                        'pending_orders' => 1,
                        'shipped_orders' => 1,
                        'delivered_orders' => 1,
                    ],
                ]);
    }

    /** @test */
    public function can_get_available_order_statuses()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/orders/statuses');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                ]);
    }

    /** @test */
    public function order_validation_requires_customer_id()
    {
        $orderData = [
            'shipping_address' => '123 Main St',
            'billing_address' => '123 Main St',
            'items' => [
                [
                    'product_id' => $this->product1->id,
                    'quantity' => 1,
                ],
            ],
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/orders', $orderData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['customer_id']);
    }

    /** @test */
    public function order_validation_requires_shipping_address()
    {
        $orderData = [
            'customer_id' => $this->customer->id,
            'billing_address' => '123 Main St',
            'items' => [
                [
                    'product_id' => $this->product1->id,
                    'quantity' => 1,
                ],
            ],
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/orders', $orderData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['shipping_address']);
    }

    /** @test */
    public function order_validation_requires_items()
    {
        $orderData = [
            'customer_id' => $this->customer->id,
            'shipping_address' => '123 Main St',
            'billing_address' => '123 Main St',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/orders', $orderData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['items']);
    }

    /** @test */
    public function order_validation_requires_valid_product_id()
    {
        $orderData = [
            'customer_id' => $this->customer->id,
            'shipping_address' => '123 Main St',
            'billing_address' => '123 Main St',
            'items' => [
                [
                    'product_id' => 99999, // Non-existent product
                    'quantity' => 1,
                ],
            ],
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/orders', $orderData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['items.0.product_id']);
    }

    /** @test */
    public function order_validation_requires_positive_quantity()
    {
        $orderData = [
            'customer_id' => $this->customer->id,
            'shipping_address' => '123 Main St',
            'billing_address' => '123 Main St',
            'items' => [
                [
                    'product_id' => $this->product1->id,
                    'quantity' => 0,
                ],
            ],
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/orders', $orderData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['items.0.quantity']);
    }

    /** @test */
    public function cannot_create_order_with_insufficient_stock()
    {
        $lowStockProduct = Product::create([
            'name' => 'Low Stock Product',
            'description' => 'Low Stock Description',
            'price' => 29.99,
            'category' => 'Electronics',
            'stock_quantity' => 1,
            'sku' => 'SKU003',
            'created_by' => $this->admin->id,
        ]);

        $orderData = [
            'customer_id' => $this->customer->id,
            'shipping_address' => '123 Main St',
            'billing_address' => '123 Main St',
            'items' => [
                [
                    'product_id' => $lowStockProduct->id,
                    'quantity' => 5, // More than available stock
                ],
            ],
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/orders', $orderData);

        $response->assertStatus(500)
                ->assertJson([
                    'success' => false,
                    'message' => 'Failed to create order',
                ]);
    }
}
