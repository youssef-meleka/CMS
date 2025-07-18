<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Services\OrderService;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
        $this->middleware('can:manage orders');
    }

    public function index(Request $request)
    {
        $query = Order::with(['customer', 'assignedUser', 'orderItems.product']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(15);
        $users = User::role(['admin', 'manager'])->get();
        $statuses = $this->orderService->getAvailableStatuses();

        return view('dashboard.orders.index', compact('orders', 'users', 'statuses'));
    }

    public function create()
    {
        $products = Product::where('stock_quantity', '>', 0)->get();
        return view('dashboard.orders.create', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            $orderData = $request->only(['customer_name', 'customer_email', 'customer_phone']);
            $orderData['created_by'] = auth()->id();
            $items = $request->items;

            $order = $this->orderService->createOrder($orderData, $items, auth()->user());

            return redirect()->route('dashboard.orders.index')
                ->with('success', 'Order created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating order: ' . $e->getMessage());
        }
    }

    public function show(Order $order)
    {
        $order->load(['customer', 'assignedUser', 'orderItems.product']);
        $users = User::role(['admin', 'manager'])->get();
        return view('dashboard.orders.show', compact('order', 'users'));
    }

    public function edit(Order $order)
    {
        $order->load(['orderItems.product']);
        $products = Product::all();
        $users = User::role(['admin', 'manager'])->get();
        $statuses = $this->orderService->getAvailableStatuses();

        return view('dashboard.orders.edit', compact('order', 'products', 'users', 'statuses'));
    }

    public function update(Request $request, Order $order)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $data = $request->only(['customer_name', 'customer_email', 'customer_phone', 'status', 'assigned_to']);
        $order->update($data);

        return redirect()->route('dashboard.orders.index')
            ->with('success', 'Order updated successfully.');
    }

    public function destroy(Order $order)
    {
        $order->delete();

        return redirect()->route('dashboard.orders.index')
            ->with('success', 'Order deleted successfully.');
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        ]);

        $order->update(['status' => $request->status]);

        return redirect()->back()
            ->with('success', 'Order status updated successfully.');
    }

    public function assign(Request $request, Order $order)
    {
        $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $order->update(['assigned_to' => $request->assigned_to]);

        return redirect()->back()
            ->with('success', 'Order assigned successfully.');
    }

    public function statistics()
    {
        $stats = $this->orderService->getOrderStatistics();
        return view('dashboard.orders.statistics', compact('stats'));
    }
}
