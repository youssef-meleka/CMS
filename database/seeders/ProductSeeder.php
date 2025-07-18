<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminUser = User::where('role', 'admin')->first();

        $products = [
            [
                'name' => 'Laptop Computer',
                'description' => 'High-performance laptop for business and gaming',
                'price' => 999.99,
                'category' => 'Electronics',
                'stock_quantity' => 50,
                'sku' => 'LAPTOP001',
                'image_url' => 'https://example.com/laptop.jpg',
                'is_active' => true,
                'created_by' => $adminUser->id,
            ],
            [
                'name' => 'Wireless Mouse',
                'description' => 'Ergonomic wireless mouse with precision tracking',
                'price' => 29.99,
                'category' => 'Electronics',
                'stock_quantity' => 100,
                'sku' => 'MOUSE001',
                'image_url' => 'https://example.com/mouse.jpg',
                'is_active' => true,
                'created_by' => $adminUser->id,
            ],
            [
                'name' => 'Office Chair',
                'description' => 'Comfortable ergonomic office chair',
                'price' => 199.99,
                'category' => 'Furniture',
                'stock_quantity' => 25,
                'sku' => 'CHAIR001',
                'image_url' => 'https://example.com/chair.jpg',
                'is_active' => true,
                'created_by' => $adminUser->id,
            ],
            [
                'name' => 'Desk Lamp',
                'description' => 'LED desk lamp with adjustable brightness',
                'price' => 49.99,
                'category' => 'Lighting',
                'stock_quantity' => 75,
                'sku' => 'LAMP001',
                'image_url' => 'https://example.com/lamp.jpg',
                'is_active' => true,
                'created_by' => $adminUser->id,
            ],
            [
                'name' => 'Notebook Set',
                'description' => 'Set of 3 premium notebooks',
                'price' => 15.99,
                'category' => 'Stationery',
                'stock_quantity' => 200,
                'sku' => 'NOTEBOOK001',
                'image_url' => 'https://example.com/notebook.jpg',
                'is_active' => true,
                'created_by' => $adminUser->id,
            ],
            [
                'name' => 'Bluetooth Headphones',
                'description' => 'Noise-cancelling wireless headphones',
                'price' => 149.99,
                'category' => 'Electronics',
                'stock_quantity' => 30,
                'sku' => 'HEADPHONES001',
                'image_url' => 'https://example.com/headphones.jpg',
                'is_active' => true,
                'created_by' => $adminUser->id,
            ],
            [
                'name' => 'Standing Desk',
                'description' => 'Adjustable height standing desk',
                'price' => 399.99,
                'category' => 'Furniture',
                'stock_quantity' => 15,
                'sku' => 'DESK001',
                'image_url' => 'https://example.com/desk.jpg',
                'is_active' => true,
                'created_by' => $adminUser->id,
            ],
            [
                'name' => 'Wireless Keyboard',
                'description' => 'Mechanical wireless keyboard',
                'price' => 79.99,
                'category' => 'Electronics',
                'stock_quantity' => 60,
                'sku' => 'KEYBOARD001',
                'image_url' => 'https://example.com/keyboard.jpg',
                'is_active' => true,
                'created_by' => $adminUser->id,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
