<?php

declare(strict_types=1);

namespace Flex\Features\Shopping\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Order extends Model
{
    protected $table = 'shopping_orders';

    protected $fillable = [
        'number',
        'customer_id',
        'status',
        'payment_status',
        'payment_method',
        'shipping_method',
        'currency',
        'subtotal',
        'discount_total',
        'shipping_total',
        'tax_total',
        'total',
        'customer_email',
        'customer_phone',
        'billing_address',
        'shipping_address',
        'customer_note',
        'admin_note',
        'placed_at',
        'paid_at',
        'completed_at',
        'cancelled_at',
    ];

    protected $casts = [
        'customer_id' => 'integer',
        'subtotal' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'shipping_total' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'total' => 'decimal:2',
        'billing_address' => 'array',
        'shipping_address' => 'array',
        'placed_at' => 'datetime',
        'paid_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class, 'order_id')
            ->orderBy('created_at');
    }

    public function couponUsages(): HasMany
    {
        return $this->hasMany(CouponUsage::class, 'order_id');
    }
}
