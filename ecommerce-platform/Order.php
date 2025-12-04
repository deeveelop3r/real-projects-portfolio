<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_amount',
        'status',
        'payment_status',
        'shipping_address',
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'shipping_address' => 'array',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';

    const PAYMENT_STATUS_PENDING = 'pending';
    const PAYMENT_STATUS_COMPLETED = 'completed';
    const PAYMENT_STATUS_FAILED = 'failed';
    const PAYMENT_STATUS_REFUNDED = 'refunded';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function calculateTotal()
    {
        return $this->items->sum(function($item) {
            return $item->price * $item->quantity;
        });
    }

    public function markAsPaid()
    {
        $this->payment_status = self::PAYMENT_STATUS_COMPLETED;
        $this->status = self::STATUS_PROCESSING;
        $this->save();
    }

    public function canBeCancelled()
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PROCESSING]);
    }
}
