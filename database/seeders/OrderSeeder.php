<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = User::where('role', 'employee')->take(3)->get();
        $products = Product::all();
        $manager = User::where('role', 'manager')->first();

        foreach ($customers as $index => $customer) {
            // Create order
            $order = Order::create([
                'order_number' => 'ORD-' . strtoupper(uniqid()),
                'customer_id' => $customer->id,
                'total_amount' => 0, // Will be calculated after items
                'status' => ['pending', 'processing', 'shipped'][$index % 3],
                'shipping_address' => "123 Main St, City, State 12345",
                'billing_address' => "123 Main St, City, State 12345",
                'notes' => "Sample order for {$customer->name}",
                'assigned_to' => $manager->id,
            ]);

            // Add random items to order
            $selectedProducts = $products->random(rand(1, 3));
            $totalAmount = 0;

            foreach ($selectedProducts as $product) {
                $quantity = rand(1, 3);
                $unitPrice = $product->price;
                $totalPrice = $quantity * $unitPrice;
                $totalAmount += $totalPrice;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                ]);
            }

            // Update order total
            $order->update(['total_amount' => $totalAmount]);
        }

        // Create additional orders with different statuses
        $additionalOrders = [
            [
                'status' => 'delivered',
                'shipped_at' => now()->subDays(5),
                'delivered_at' => now()->subDays(2),
            ],
            [
                'status' => 'cancelled',
                'notes' => 'Customer requested cancellation',
            ],
        ];

        foreach ($additionalOrders as $orderData) {
            $customer = $customers->random();

            $order = Order::create([
                'order_number' => 'ORD-' . strtoupper(uniqid()),
                'customer_id' => $customer->id,
                'total_amount' => 0,
                'status' => $orderData['status'],
                'shipping_address' => "456 Oak Ave, City, State 54321",
                'billing_address' => "456 Oak Ave, City, State 54321",
                'notes' => $orderData['notes'] ?? "Additional sample order",
                'assigned_to' => $manager->id,
                'shipped_at' => $orderData['shipped_at'] ?? null,
                'delivered_at' => $orderData['delivered_at'] ?? null,
            ]);

            if ($orderData['status'] !== 'cancelled') {
                $selectedProducts = $products->random(rand(1, 2));
                $totalAmount = 0;

                foreach ($selectedProducts as $product) {
                    $quantity = rand(1, 2);
                    $unitPrice = $product->price;
                    $totalPrice = $quantity * $unitPrice;
                    $totalAmount += $totalPrice;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total_price' => $totalPrice,
                    ]);
                }

                $order->update(['total_amount' => $totalAmount]);
            }
        }
    }
}
