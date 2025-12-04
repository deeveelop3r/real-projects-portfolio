<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CartItem;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function createOrderFromCart($user, $shippingAddress, $notes = null)
    {
        $cartItems = CartItem::where('user_id', $user->id)->with('product')->get();

        if ($cartItems->isEmpty()) {
            throw new \Exception('Cart is empty');
        }

        // Calculate total
        $total = $cartItems->sum(function($item) {
            return $item->product->price * $item->quantity;
        });

        // Validate stock
        foreach ($cartItems as $item) {
            if ($item->product->stock < $item->quantity) {
                throw new \Exception("Insufficient stock for {$item->product->name}");
            }
        }

        DB::beginTransaction();

        try {
            // Create order
            $order = Order::create([
                'user_id' => $user->id,
                'total_amount' => $total,
                'status' => Order::STATUS_PENDING,
                'payment_status' => Order::PAYMENT_STATUS_PENDING,
                'shipping_address' => $shippingAddress,
                'notes' => $notes,
            ]);

            // Create order items and reduce stock
            foreach ($cartItems as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product->id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->product->price,
                    'subtotal' => $cartItem->product->price * $cartItem->quantity,
                ]);

                // Decrease stock
                $cartItem->product->decreaseStock($cartItem->quantity);
            }

            // Clear cart
            CartItem::where('user_id', $user->id)->delete();

            DB::commit();

            return $order;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function processRefund(Order $order)
    {
        DB::beginTransaction();

        try {
            foreach ($order->items as $item) {
                $item->product->increaseStock($item->quantity);
            }

            $order->payment_status = Order::PAYMENT_STATUS_REFUNDED;
            $order->status = Order::STATUS_CANCELLED;
            $order->save();

            DB::commit();

            return $order;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateOrderStatus($order, $status)
    {
        $validStatuses = [
            Order::STATUS_PENDING,
            Order::STATUS_PROCESSING,
            Order::STATUS_SHIPPED,
            Order::STATUS_DELIVERED,
            Order::STATUS_CANCELLED,
        ];

        if (!in_array($status, $validStatuses)) {
            throw new \Exception('Invalid order status');
        }

        $order->status = $status;
        $order->save();

        return $order;
    }
}
