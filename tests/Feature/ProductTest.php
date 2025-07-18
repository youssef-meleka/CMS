<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $manager;
    protected User $employee;
    protected User $customer;
    protected string $adminToken;
    protected string $managerToken;
    protected string $employeeToken;
    protected string $customerToken;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupRolesAndPermissions();
        $this->createUsers();
        $this->createTokens();
    }

    private function setupRolesAndPermissions(): void
    {
        // Create permissions
        $permissions = [
            'access dashboard', 'manage users', 'manage products', 'manage orders',
            'view products', 'view orders', 'view own orders', 'create products',
            'edit products', 'delete products', 'create orders', 'edit orders',
            'delete orders', 'update order status', 'assign orders', 'view statistics',
            'manage product stock',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles with permissions
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(Permission::all());

        $manager = Role::firstOrCreate(['name' => 'manager']);
        $manager->syncPermissions([
            'view products', 'create products', 'edit products', 'delete products',
            'manage product stock', 'view orders', 'create orders', 'edit orders',
            'delete orders', 'update order status', 'assign orders', 'view statistics'
        ]);

        $employee = Role::firstOrCreate(['name' => 'employee']);
        $employee->syncPermissions([
            'view products', 'view own orders', 'create orders'
        ]);

        $customer = Role::firstOrCreate(['name' => 'customer']);
        $customer->syncPermissions([
            'view products', 'view own orders', 'create orders'
        ]);
    }

    private function createUsers(): void
    {
        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => Hash::make('Password0!'),
            'is_active' => true,
        ]);
        $this->admin->assignRole('admin');

        $this->manager = User::create([
            'name' => 'Manager User',
            'email' => 'manager@test.com',
            'password' => Hash::make('Password0!'),
            'is_active' => true,
        ]);
        $this->manager->assignRole('manager');

        $this->employee = User::create([
            'name' => 'Employee User',
            'email' => 'employee@test.com',
            'password' => Hash::make('Password0!'),
            'is_active' => true,
        ]);
        $this->employee->assignRole('employee');

        $this->customer = User::create([
            'name' => 'Customer User',
            'email' => 'customer@test.com',
            'password' => Hash::make('Password0!'),
            'is_active' => true,
        ]);
        $this->customer->assignRole('customer');
    }

    private function createTokens(): void
    {
        $this->adminToken = $this->admin->createToken('test-token')->plainTextToken;
        $this->managerToken = $this->manager->createToken('test-token')->plainTextToken;
        $this->employeeToken = $this->employee->createToken('test-token')->plainTextToken;
        $this->customerToken = $this->customer->createToken('test-token')->plainTextToken;
    }

    /** @test */
    public function admin_can_create_product()
    {
        $productData = [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 99.99,
            'category' => 'Electronics',
            'stock_quantity' => 50,
            'image_url' => 'https://example.com/image.jpg',
            'is_active' => true,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/products', $productData);

        $response->assertStatus(201)
                ->assertJson([
                    'success' => true,
                    'message' => 'Product created successfully',
                ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'price' => 99.99,
            'category' => 'Electronics',
            'created_by' => $this->admin->id,
        ]);
    }

    /** @test */
    public function manager_can_create_product()
    {
        $productData = [
            'name' => 'Manager Product',
            'description' => 'Manager Description',
            'price' => 149.99,
            'category' => 'Furniture',
            'stock_quantity' => 25,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->managerToken,
        ])->postJson('/api/products', $productData);

        $response->assertStatus(201)
                ->assertJson([
                    'success' => true,
                    'message' => 'Product created successfully',
                ]);
    }

    /** @test */
    public function employee_cannot_create_product()
    {
        $productData = [
            'name' => 'Employee Product',
            'description' => 'Employee Description',
            'price' => 29.99,
            'category' => 'Stationery',
            'stock_quantity' => 100,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->employeeToken,
        ])->postJson('/api/products', $productData);

        $response->assertStatus(403);
    }

    /** @test */
    public function customer_cannot_create_product()
    {
        $productData = [
            'name' => 'Customer Product',
            'description' => 'Customer Description',
            'price' => 19.99,
            'category' => 'Books',
            'stock_quantity' => 75,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->customerToken,
        ])->postJson('/api/products', $productData);

        $response->assertStatus(403);
    }

    /** @test */
    public function unauthenticated_user_cannot_create_product()
    {
        $productData = [
            'name' => 'Unauthorized Product',
            'description' => 'Unauthorized Description',
            'price' => 19.99,
            'category' => 'Books',
            'stock_quantity' => 75,
        ];

        $response = $this->postJson('/api/products', $productData);

        $response->assertStatus(401);
    }

    /** @test */
    public function can_list_products()
    {
        // Create some test products
        Product::create([
            'name' => 'Product 1',
            'description' => 'Description 1',
            'price' => 99.99,
            'category' => 'Electronics',
            'stock_quantity' => 50,
            'sku' => 'SKU001',
            'created_by' => $this->admin->id,
        ]);

        Product::create([
            'name' => 'Product 2',
            'description' => 'Description 2',
            'price' => 149.99,
            'category' => 'Furniture',
            'stock_quantity' => 25,
            'sku' => 'SKU002',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/products');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                ])
                ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function can_get_single_product()
    {
        $product = Product::create([
            'name' => 'Single Product',
            'description' => 'Single Description',
            'price' => 199.99,
            'category' => 'Electronics',
            'stock_quantity' => 10,
            'sku' => 'SKU003',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'id' => $product->id,
                        'name' => 'Single Product',
                        'price' => '199.99',
                    ],
                ])
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'name',
                        'description',
                        'price',
                        'category',
                        'stock_quantity',
                        'sku',
                        'image_url',
                        'is_active',
                        'created_by',
                        'creator',
                        'stock_status',
                        'order_statistics',
                        'created_at',
                        'updated_at',
                    ],
                ]);
    }

    /** @test */
    public function can_update_product()
    {
        $product = Product::create([
            'name' => 'Original Product',
            'description' => 'Original Description',
            'price' => 99.99,
            'category' => 'Electronics',
            'stock_quantity' => 50,
            'sku' => 'SKU004',
            'created_by' => $this->admin->id,
        ]);

        $updateData = [
            'name' => 'Updated Product',
            'description' => 'Updated Description',
            'price' => 149.99,
            'category' => 'Furniture',
            'stock_quantity' => 75,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->putJson("/api/products/{$product->id}", $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Product updated successfully',
                ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product',
            'price' => 149.99,
            'category' => 'Furniture',
        ]);
    }

    /** @test */
    public function can_delete_product()
    {
        $product = Product::create([
            'name' => 'Product to Delete',
            'description' => 'Description',
            'price' => 99.99,
            'category' => 'Electronics',
            'stock_quantity' => 50,
            'sku' => 'SKU005',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Product deleted successfully',
                ]);

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }

    /** @test */
    public function can_search_products()
    {
        Product::create([
            'name' => 'Laptop Computer',
            'description' => 'High-performance laptop',
            'price' => 999.99,
            'category' => 'Electronics',
            'stock_quantity' => 10,
            'sku' => 'SKU006',
            'created_by' => $this->admin->id,
        ]);

        Product::create([
            'name' => 'Desktop Computer',
            'description' => 'Desktop workstation',
            'price' => 1299.99,
            'category' => 'Electronics',
            'stock_quantity' => 5,
            'sku' => 'SKU007',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/products?search=laptop');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                ])
                ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function can_filter_products_by_category()
    {
        Product::create([
            'name' => 'Electronics Product',
            'description' => 'Electronics description',
            'price' => 199.99,
            'category' => 'Electronics',
            'stock_quantity' => 20,
            'sku' => 'SKU008',
            'created_by' => $this->admin->id,
        ]);

        Product::create([
            'name' => 'Furniture Product',
            'description' => 'Furniture description',
            'price' => 299.99,
            'category' => 'Furniture',
            'stock_quantity' => 15,
            'sku' => 'SKU009',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/products?category=Electronics');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                ])
                ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function can_get_product_categories()
    {
        Product::create([
            'name' => 'Product 1',
            'description' => 'Description 1',
            'price' => 99.99,
            'category' => 'Electronics',
            'stock_quantity' => 50,
            'sku' => 'SKU010',
            'created_by' => $this->admin->id,
        ]);

        Product::create([
            'name' => 'Product 2',
            'description' => 'Description 2',
            'price' => 149.99,
            'category' => 'Furniture',
            'stock_quantity' => 25,
            'sku' => 'SKU011',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/products/categories');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                ])
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'category',
                            'count',
                        ],
                    ],
                ]);
    }

    /** @test */
    public function can_get_low_stock_products()
    {
        Product::create([
            'name' => 'Low Stock Product',
            'description' => 'Low stock description',
            'price' => 99.99,
            'category' => 'Electronics',
            'stock_quantity' => 5,
            'sku' => 'SKU012',
            'created_by' => $this->admin->id,
        ]);

        Product::create([
            'name' => 'Normal Stock Product',
            'description' => 'Normal stock description',
            'price' => 149.99,
            'category' => 'Furniture',
            'stock_quantity' => 50,
            'sku' => 'SKU013',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/products/low-stock?threshold=10');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                ])
                ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function can_update_product_stock()
    {
        $product = Product::create([
            'name' => 'Stock Update Product',
            'description' => 'Stock update description',
            'price' => 99.99,
            'category' => 'Electronics',
            'stock_quantity' => 10,
            'sku' => 'SKU014',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->patchJson("/api/products/{$product->id}/stock", [
            'stock_quantity' => 25,
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Stock updated successfully',
                ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_quantity' => 25,
        ]);
    }

    /** @test */
    public function product_validation_requires_name()
    {
        $productData = [
            'description' => 'Test Description',
            'price' => 99.99,
            'category' => 'Electronics',
            'stock_quantity' => 50,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/products', $productData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function product_validation_requires_positive_price()
    {
        $productData = [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => -10,
            'category' => 'Electronics',
            'stock_quantity' => 50,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/products', $productData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['price']);
    }

    /** @test */
    public function product_validation_requires_positive_stock()
    {
        $productData = [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 99.99,
            'category' => 'Electronics',
            'stock_quantity' => -5,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/products', $productData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['stock_quantity']);
    }
}
