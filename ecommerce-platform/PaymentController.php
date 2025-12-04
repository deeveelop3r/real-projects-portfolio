<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Process payment with Stripe
     */
    public function processStripePayment(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'token' => 'required|string',
            'payment_method' => 'required|in:credit_card,debit_card',
        ]);

        $order = Order::findOrFail($validated['order_id']);
        $this->authorize('update', $order);

        if ($order->payment_status !== Order::PAYMENT_STATUS_PENDING) {
            return response()->json([
                'success' => false,
                'message' => 'Payment already processed',
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Simulate Stripe payment processing
            // In production, use Stripe SDK
            $transactionId = $this->createStripeTransaction(
                $order->total_amount,
                $validated['token']
            );

            // Record payment
            $payment = Payment::create([
                'order_id' => $order->id,
                'amount' => $order->total_amount,
                'payment_method' => 'stripe',
                'payment_type' => $validated['payment_method'],
                'transaction_id' => $transactionId,
                'status' => Payment::STATUS_COMPLETED,
                'response' => json_encode(['transaction_id' => $transactionId]),
            ]);

            // Update order
            $order->payment_status = Order::PAYMENT_STATUS_COMPLETED;
            $order->status = Order::STATUS_PROCESSING;
            $order->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'data' => [
                    'order_id' => $order->id,
                    'transaction_id' => $transactionId,
                    'amount' => $order->total_amount,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Payment failed: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Process payment with PayPal
     */
    public function processPayPalPayment(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'paypal_token' => 'required|string',
        ]);

        $order = Order::findOrFail($validated['order_id']);
        $this->authorize('update', $order);

        try {
            DB::beginTransaction();

            $transactionId = $this->createPayPalTransaction(
                $order->total_amount,
                $validated['paypal_token']
            );

            $payment = Payment::create([
                'order_id' => $order->id,
                'amount' => $order->total_amount,
                'payment_method' => 'paypal',
                'transaction_id' => $transactionId,
                'status' => Payment::STATUS_COMPLETED,
                'response' => json_encode(['transaction_id' => $transactionId]),
            ]);

            $order->payment_status = Order::PAYMENT_STATUS_COMPLETED;
            $order->status = Order::STATUS_PROCESSING;
            $order->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'PayPal payment processed successfully',
                'data' => [
                    'order_id' => $order->id,
                    'transaction_id' => $transactionId,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'PayPal payment failed: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get payment status
     */
    public function status(Order $order)
    {
        $this->authorize('view', $order);

        $payment = $order->payment;

        if (!$payment) {
            return response()->json([
                'success' => true,
                'data' => [
                    'status' => 'pending',
                    'message' => 'No payment recorded',
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'status' => $payment->status,
                'amount' => $payment->amount,
                'transaction_id' => $payment->transaction_id,
                'payment_method' => $payment->payment_method,
                'created_at' => $payment->created_at,
            ],
        ]);
    }

    /**
     * Mock Stripe transaction (for demonstration)
     */
    private function createStripeTransaction($amount, $token)
    {
        // In production: use Stripe SDK
        // $charge = \Stripe\Charge::create([...]);
        
        return 'stripe_' . uniqid();
    }

    /**
     * Mock PayPal transaction (for demonstration)
     */
    private function createPayPalTransaction($amount, $token)
    {
        // In production: use PayPal SDK
        // $sale = $paypal->sale()->create([...]);
        
        return 'paypal_' . uniqid();
    }
}
