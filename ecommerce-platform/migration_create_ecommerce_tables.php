<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('long_description')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('stock')->default(0);
            $table->string('category');
            $table->string('sku')->unique();
            $table->string('image_url')->nullable();
            $table->json('images')->nullable();
            $table->json('specifications')->nullable();
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('reviews_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index('category');
            $table->index('sku');
            $table->index('is_active');
            $table->index('created_at');
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('total_amount', 10, 2);
            $table->enum('status', [
                'pending',
                'processing',
                'shipped',
                'delivered',
                'cancelled'
            ])->default('pending');
            $table->enum('payment_status', [
                'pending',
                'completed',
                'failed',
                'refunded'
            ])->default('pending');
            $table->json('shipping_address');
            $table->text('notes')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id');
            $table->index('status');
            $table->index('payment_status');
            $table->index('created_at');
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
            $table->integer('quantity');
            $table->decimal('price', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();

            $table->index('order_id');
            $table->index('product_id');
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->timestamps();

            $table->unique(['user_id', 'product_id']);
            $table->index('user_id');
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->string('payment_method'); // stripe, paypal, bank_transfer
            $table->string('transaction_id')->unique();
            $table->enum('status', [
                'pending',
                'completed',
                'failed',
                'cancelled'
            ])->default('pending');
            $table->string('payment_type')->nullable(); // credit_card, debit_card, etc
            $table->json('response')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('order_id');
            $table->index('transaction_id');
            $table->index('status');
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('rating')->min(1)->max(5);
            $table->text('comment');
            $table->boolean('is_verified')->default(false);
            $table->integer('helpful_count')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'user_id']);
            $table->index('product_id');
            $table->index('rating');
        });

        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['user_id', 'product_id']);
            $table->index('user_id');
        });

        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->decimal('discount_value', 10, 2);
            $table->enum('discount_type', ['percentage', 'fixed'])->default('fixed');
            $table->integer('max_uses')->nullable();
            $table->integer('current_uses')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('valid_from');
            $table->timestamp('valid_until')->nullable();
            $table->timestamps();

            $table->index('code');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('wishlists');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('products');
    }
};
