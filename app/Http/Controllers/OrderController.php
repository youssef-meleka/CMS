<?php

namespace App\Http\Controllers;

use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Requests\Order\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Repositories\UserRepository;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
        $this->middleware('auth:sanctum');
        $this->middleware('can:view orders')->only(['index', 'show', 'statuses']);
        $this->middleware('can:create orders')->only(['store']);
        $this->middleware('can:edit orders')->only(['update']);
        $this->middleware('can:delete orders')->only(['destroy']);
        $this->middleware('can:update order status')->only(['updateStatus']);
        $this->middleware('can:assign orders')->only(['assignOrder']);
        $this->middleware('can:view statistics')->only(['statistics']);
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $status = $request->get('status');
            $customerId = $request->get('customer_id');
            $assignedTo = $request->get('assigned_to');

            if ($status) {
                $orders = $this->orderService->getOrdersByStatus($status, $perPage);
            } elseif ($customerId) {
                $orders = $this->orderService->getOrdersByCustomer($customerId, $perPage);
            } elseif ($assignedTo) {
                $orders = $this->orderService->getOrdersAssignedToUser($assignedTo, $perPage);
            } else {
                $orders = $this->orderService->getPaginatedOrders($perPage);
            }

            return response()->json([
                'success' => true,
                'data' => OrderResource::collection($orders->items()),
                'pagination' => [
                    'current_page' => $orders->currentPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                    'last_page' => $orders->lastPage(),
                    'from' => $orders->firstItem(),
                    'to' => $orders->lastItem(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $items = $data['items'];
            unset($data['items']);

            $customer = Auth::user();
            if (isset($data['customer_id'])) {
                $customer = app(UserRepository::class)->findById($data['customer_id']);
            }

            $order = $this->orderService->createOrder($data, $items, $customer);

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => new OrderResource($order)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $order = $this->orderService->getOrderById($id);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new OrderResource($order)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(UpdateOrderRequest $request, int $id): JsonResponse
    {
        try {
            $updated = $this->orderService->updateOrder($id, $request->validated());

            if (!$updated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            $order = $this->orderService->getOrderById($id);

            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully',
                'data' => new OrderResource($order)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->orderService->deleteOrder($id);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Order deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'status' => 'required|in:pending,processing,shipped,delivered,cancelled'
            ]);

            $updated = $this->orderService->updateOrderStatus($id, $request->status);

            if (!$updated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function assign(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'user_id' => 'required|integer|exists:users,id'
            ]);

            $assigned = $this->orderService->assignOrderToUser($id, $request->user_id);

            if (!$assigned) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Order assigned successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function statistics(): JsonResponse
    {
        try {
            $statistics = $this->orderService->getOrderStatistics();

            return response()->json([
                'success' => true,
                'data' => $statistics
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve order statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function statuses(): JsonResponse
    {
        try {
            $statuses = $this->orderService->getAvailableStatuses();

            return response()->json([
                'success' => true,
                'data' => $statuses
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve order statuses',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
