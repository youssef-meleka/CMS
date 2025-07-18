<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\User;
use App\Repositories\ProductRepository;
use App\Services\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ProductService $productService;
    protected ProductRepository $productRepository;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles and permissions
        $this->setupRolesAndPermissions();

        $this->productRepository = new ProductRepository();
        $this->productService = new ProductService($this->productRepository);

        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('Password0!'),
        ]);
        $this->user->assignRole('admin');
    }

    private function setupRolesAndPermissions(): void
    {
        // Create permissions
        Permission::create(['name' => 'manage products']);
        Permission::create(['name' => 'view products']);

        // Create roles
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo(['manage products', 'view products']);
    }

    /** @test */
    public function can_create_product()
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

        $product = $this->productService->createProduct($productData, $this->user);

        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals('Test Product', $product->name);
        $this->assertEquals(99.99, $product->price);
        $this->assertEquals('Electronics', $product->category);
        $this->assertEquals(50, $product->stock_quantity);
        $this->assertEquals($this->user->id, $product->created_by);
        $this->assertNotEmpty($product->sku);
    }

    /** @test */
    public function can_get_product_by_id()
    {
        $product = Product::create([
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 99.99,
            'category' => 'Electronics',
            'stock_quantity' => 50,
            'sku' => 'SKU001',
            'created_by' => $this->user->id,
        ]);

        $retrievedProduct = $this->productService->getProductById($product->id);

        $this->assertInstanceOf(Product::class, $retrievedProduct);
        $this->assertEquals($product->id, $retrievedProduct->id);
        $this->assertEquals('Test Product', $retrievedProduct->name);
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
            'sku' => 'SKU002',
            'created_by' => $this->user->id,
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'price' => 149.99,
            'stock_quantity' => 75,
        ];

        $result = $this->productService->updateProduct($product->id, $updateData);

        $this->assertTrue($result);

        $updatedProduct = $this->productService->getProductById($product->id);
        $this->assertEquals('Updated Name', $updatedProduct->name);
        $this->assertEquals(149.99, $updatedProduct->price);
        $this->assertEquals(75, $updatedProduct->stock_quantity);
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
            'sku' => 'SKU003',
            'created_by' => $this->user->id,
        ]);

        $result = $this->productService->deleteProduct($product->id);

        $this->assertTrue($result);

        $deletedProduct = $this->productService->getProductById($product->id);
        $this->assertNull($deletedProduct);
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
            'sku' => 'SKU004',
            'created_by' => $this->user->id,
        ]);

        Product::create([
            'name' => 'Office Chair',
            'description' => 'Comfortable chair',
            'price' => 199.99,
            'category' => 'Furniture',
            'stock_quantity' => 25,
            'sku' => 'SKU005',
            'created_by' => $this->user->id,
        ]);

        $results = $this->productService->searchProducts('laptop');

        $this->assertCount(1, $results);
        $this->assertEquals('Laptop Computer', $results->first()->name);
    }

    /** @test */
    public function can_get_products_by_category()
    {
        Product::create([
            'name' => 'Electronics Product',
            'description' => 'Electronics Description',
            'price' => 299.99,
            'category' => 'Electronics',
            'stock_quantity' => 15,
            'sku' => 'SKU006',
            'created_by' => $this->user->id,
        ]);

        Product::create([
            'name' => 'Furniture Product',
            'description' => 'Furniture Description',
            'price' => 399.99,
            'category' => 'Furniture',
            'stock_quantity' => 8,
            'sku' => 'SKU007',
            'created_by' => $this->user->id,
        ]);

        $electronicsProducts = $this->productService->getProductsByCategory('Electronics');

        $this->assertCount(1, $electronicsProducts);
        $this->assertEquals('Electronics Product', $electronicsProducts->first()->name);
    }

    /** @test */
    public function can_get_active_products()
    {
        Product::create([
            'name' => 'Active Product',
            'description' => 'Active Description',
            'price' => 99.99,
            'category' => 'Electronics',
            'stock_quantity' => 50,
            'sku' => 'SKU008',
            'is_active' => true,
            'created_by' => $this->user->id,
        ]);

        Product::create([
            'name' => 'Inactive Product',
            'description' => 'Inactive Description',
            'price' => 149.99,
            'category' => 'Furniture',
            'stock_quantity' => 25,
            'sku' => 'SKU009',
            'is_active' => false,
            'created_by' => $this->user->id,
        ]);

        $activeProducts = $this->productService->getActiveProducts();

        $this->assertCount(1, $activeProducts);
        $this->assertEquals('Active Product', $activeProducts->first()->name);
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
            'sku' => 'SKU010',
            'created_by' => $this->user->id,
        ]);

        Product::create([
            'name' => 'High Stock Product',
            'description' => 'High Stock Description',
            'price' => 79.99,
            'category' => 'Furniture',
            'stock_quantity' => 50,
            'sku' => 'SKU011',
            'created_by' => $this->user->id,
        ]);

        $lowStockProducts = $this->productService->getLowStockProducts(10);

        $this->assertCount(1, $lowStockProducts);
        $this->assertEquals('Low Stock Product', $lowStockProducts->first()->name);
    }

    /** @test */
    public function can_update_stock()
    {
        $product = Product::create([
            'name' => 'Stock Product',
            'description' => 'Stock Description',
            'price' => 89.99,
            'category' => 'Electronics',
            'stock_quantity' => 20,
            'sku' => 'SKU012',
            'created_by' => $this->user->id,
        ]);

        $result = $this->productService->updateStock($product->id, 35);

        $this->assertTrue($result);

        $updatedProduct = $this->productService->getProductById($product->id);
        $this->assertEquals(35, $updatedProduct->stock_quantity);
    }

    /** @test */
    public function can_decrease_stock()
    {
        $product = Product::create([
            'name' => 'Stock Product',
            'description' => 'Stock Description',
            'price' => 89.99,
            'category' => 'Electronics',
            'stock_quantity' => 50,
            'sku' => 'SKU013',
            'created_by' => $this->user->id,
        ]);

        $result = $this->productService->decreaseStock($product->id, 10);

        $this->assertTrue($result);

        $updatedProduct = $this->productService->getProductById($product->id);
        $this->assertEquals(40, $updatedProduct->stock_quantity);
    }

    /** @test */
    public function cannot_decrease_stock_below_zero()
    {
        $product = Product::create([
            'name' => 'Stock Product',
            'description' => 'Stock Description',
            'price' => 89.99,
            'category' => 'Electronics',
            'stock_quantity' => 5,
            'sku' => 'SKU014',
            'created_by' => $this->user->id,
        ]);

        $result = $this->productService->decreaseStock($product->id, 10);

        $this->assertFalse($result);

        $updatedProduct = $this->productService->getProductById($product->id);
        $this->assertEquals(5, $updatedProduct->stock_quantity); // Stock unchanged
    }

    /** @test */
    public function can_increase_stock()
    {
        $product = Product::create([
            'name' => 'Stock Product',
            'description' => 'Stock Description',
            'price' => 89.99,
            'category' => 'Electronics',
            'stock_quantity' => 20,
            'sku' => 'SKU015',
            'created_by' => $this->user->id,
        ]);

        $result = $this->productService->increaseStock($product->id, 15);

        $this->assertTrue($result);

        $updatedProduct = $this->productService->getProductById($product->id);
        $this->assertEquals(35, $updatedProduct->stock_quantity);
    }

    /** @test */
    public function can_check_product_availability()
    {
        $product = Product::create([
            'name' => 'Available Product',
            'description' => 'Available Description',
            'price' => 99.99,
            'category' => 'Electronics',
            'stock_quantity' => 10,
            'sku' => 'SKU016',
            'is_active' => true,
            'created_by' => $this->user->id,
        ]);

        $this->assertTrue($this->productService->isProductAvailable($product->id, 5));
        $this->assertFalse($this->productService->isProductAvailable($product->id, 15));
    }

    /** @test */
    public function can_get_categories()
    {
        Product::create([
            'name' => 'Product 1',
            'description' => 'Description 1',
            'price' => 99.99,
            'category' => 'Electronics',
            'stock_quantity' => 50,
            'sku' => 'SKU017',
            'created_by' => $this->user->id,
        ]);

        Product::create([
            'name' => 'Product 2',
            'description' => 'Description 2',
            'price' => 149.99,
            'category' => 'Furniture',
            'stock_quantity' => 25,
            'sku' => 'SKU018',
            'created_by' => $this->user->id,
        ]);

        Product::create([
            'name' => 'Product 3',
            'description' => 'Description 3',
            'price' => 29.99,
            'category' => 'Electronics', // Duplicate category
            'stock_quantity' => 100,
            'sku' => 'SKU019',
            'created_by' => $this->user->id,
        ]);

        $categories = $this->productService->getCategories();

        $this->assertCount(2, $categories);
        $this->assertContains(['category' => 'Electronics', 'count' => 2], $categories);
        $this->assertContains(['category' => 'Furniture', 'count' => 1], $categories);
    }

    /** @test */
    public function generates_unique_sku()
    {
        $productData1 = [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 99.99,
            'category' => 'Electronics',
            'stock_quantity' => 50,
        ];

        $productData2 = [
            'name' => 'Test Product',
            'description' => 'Test Description 2',
            'price' => 149.99,
            'category' => 'Furniture',
            'stock_quantity' => 25,
        ];

        $product1 = $this->productService->createProduct($productData1, $this->user);
        $product2 = $this->productService->createProduct($productData2, $this->user);

        $this->assertNotEquals($product1->sku, $product2->sku);
        $this->assertNotEmpty($product1->sku);
        $this->assertNotEmpty($product2->sku);
    }
}
