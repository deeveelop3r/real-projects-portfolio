<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// Public Routes
Route::middleware('throttle:60,1')->group(function () {
    // Products
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/categories', [ProductController::class, 'categories']);
    Route::get('/products/featured', [ProductController::class, 'featured']);
    Route::get('/products/{product}', [ProductController::class, 'show']);
    Route::get('/products/{product}/related', [ProductController::class, 'related']);

    // Authentication
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
});

// Protected Routes - Require Authentication
Route::middleware('auth:sanctum')->group(function () {
    // User
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::put('/user/profile', [AuthController::class, 'updateProfile']);
    Route::put('/user/password', [AuthController::class, 'changePassword']);

    // Cart
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::put('/cart/{cartItem}', [CartController::class, 'update']);
    Route::delete('/cart/{cartItem}', [CartController::class, 'destroy']);
    Route::delete('/cart', [CartController::class, 'clear']);

    // Orders
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::delete('/orders/{order}/cancel', [OrderController::class, 'cancel']);

    // Payments
    Route::post('/payments/stripe', [PaymentController::class, 'processStripePayment']);
    Route::post('/payments/paypal', [PaymentController::class, 'processPayPalPayment']);
    Route::get('/payments/{order}/status', [PaymentController::class, 'status']);

    // Wishlist (future)
    // Route::get('/wishlist', [WishlistController::class, 'index']);
    // Route::post('/wishlist', [WishlistController::class, 'store']);
    // Route::delete('/wishlist/{product}', [WishlistController::class, 'destroy']);
});

// Admin Routes
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    // Product Management
    Route::apiResource('products', 'Admin\ProductAdminController');
    Route::post('/products/{product}/toggle', 'Admin\ProductAdminController@toggle');
    Route::post('/products/{product}/feature', 'Admin\ProductAdminController@toggleFeature');

    // Order Management
    Route::get('/orders', 'Admin\OrderAdminController@index');
    Route::get('/orders/{order}', 'Admin\OrderAdminController@show');
    Route::put('/orders/{order}/status', 'Admin\OrderAdminController@updateStatus');
    Route::put('/orders/{order}/shipping', 'Admin\OrderAdminController@updateShipping');

    // Sales Reports
    Route::get('/reports/sales', 'Admin\ReportController@salesReport');
    Route::get('/reports/products', 'Admin\ReportController@productReport');
    Route::get('/reports/customers', 'Admin\ReportController@customerReport');

    // Dashboard
    Route::get('/dashboard/stats', 'Admin\DashboardController@stats');
    Route::get('/dashboard/charts', 'Admin\DashboardController@charts');
    Route::get('/dashboard/recent-orders', 'Admin\DashboardController@recentOrders');
});

// Statistics (Public)
Route::get('/statistics', [OrderController::class, 'statistics']);

// Health Check
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now(),
        'version' => '1.0.0',
    ]);
});
