<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\CartItem;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
        $this->middleware('auth:sanctum');
    }

    // Create order from cart
    public function store(Request $request)
    {
        $validated = $request->validate([
            'shipping_address' => 'required|array',
            'shipping_address.name' => 'required|string',
            'shipping_address.email' => 'required|email',
            'shipping_address.phone' => 'required|string',
            'shipping_address.address' => 'required|string',
            'shipping_address.city' => 'required|string',
            'shipping_address.country' => 'required|string',
            'shipping_address.postal_code' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $order = $this->orderService->createOrderFromCart(
                auth()->user(),
                $validated['shipping_address'],
                $validated['notes'] ?? null
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => $order->load('items'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    // Get user's orders
    public function index()
    {
        $orders = auth()->user()
            ->orders()
            ->with('items.product', 'payment')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    // Get single order
    public function show(Order $order)
    {
        $this->authorize('view', $order);

        return response()->json([
            'success' => true,
            'data' => $order->load('items.product', 'payment'),
        ]);
    }

    // Cancel order
    public function cancel(Order $order)
    {
        $this->authorize('update', $order);

        if (!$order->canBeCancelled()) {
            return response()->json([
                'success' => false,
                'message' => 'This order cannot be cancelled',
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Restore product stock
            foreach ($order->items as $item) {
                $item->product->increaseStock($item->quantity);
            }

            $order->status = Order::STATUS_CANCELLED;
            $order->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order cancelled successfully',
                'data' => $order,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    // Get order statistics
    public function statistics()
    {
        $stats = [
            'total_orders' => Order::count(),
            'total_revenue' => Order::sum('total_amount'),
            'pending_orders' => Order::where('status', Order::STATUS_PENDING)->count(),
            'average_order_value' => Order::avg('total_amount'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
