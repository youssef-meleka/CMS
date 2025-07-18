<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $manager;
    protected User $employee;
    protected string $adminToken;
    protected string $managerToken;
    protected string $employeeToken;

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

        $this->employee = User::create([
            'name' => 'Employee User',
            'email' => 'employee@test.com',
            'password' => Hash::make('Password0!'),
            'role' => 'employee',
        ]);

        // Get tokens
        $this->adminToken = $this->admin->createToken('test-token')->plainTextToken;
        $this->managerToken = $this->manager->createToken('test-token')->plainTextToken;
        $this->employeeToken = $this->employee->createToken('test-token')->plainTextToken;
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
                ]);
    }

    /** @test */
    public function can_update_product()
    {
        $product = Product::create([
            'name' => 'Original Name',
            'description' => 'Original Description',
            'price' => 99.99,
            'category' => 'Electronics',
            'stock_quantity' => 50,
            'sku' => 'SKU004',
            'created_by' => $this->admin->id,
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'price' => 149.99,
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
            'name' => 'Updated Name',
            'price' => 149.99,
            'stock_quantity' => 75,
        ]);
    }

    /** @test */
    public function can_delete_product()
    {
        $product = Product::create([
            'name' => 'To Delete',
            'description' => 'To Delete Description',
            'price' => 29.99,
            'category' => 'Books',
            'stock_quantity' => 100,
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
            'name' => 'Office Chair',
            'description' => 'Comfortable chair',
            'price' => 199.99,
            'category' => 'Furniture',
            'stock_quantity' => 25,
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
            'description' => 'Electronics Description',
            'price' => 299.99,
            'category' => 'Electronics',
            'stock_quantity' => 15,
            'sku' => 'SKU008',
            'created_by' => $this->admin->id,
        ]);

        Product::create([
            'name' => 'Furniture Product',
            'description' => 'Furniture Description',
            'price' => 399.99,
            'category' => 'Furniture',
            'stock_quantity' => 8,
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
                ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function can_get_low_stock_products()
    {
        Product::create([
            'name' => 'Low Stock Product',
            'description' => 'Low Stock Description',
            'price' => 49.99,
            'category' => 'Electronics',
            'stock_quantity' => 5,
            'sku' => 'SKU012',
            'created_by' => $this->admin->id,
        ]);

        Product::create([
            'name' => 'High Stock Product',
            'description' => 'High Stock Description',
            'price' => 79.99,
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
            'name' => 'Stock Product',
            'description' => 'Stock Description',
            'price' => 89.99,
            'category' => 'Electronics',
            'stock_quantity' => 20,
            'sku' => 'SKU014',
            'created_by' => $this->admin->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->patchJson("/api/products/{$product->id}/stock", [
            'stock_quantity' => 35,
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Stock updated successfully',
                ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_quantity' => 35,
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
