<?php

declare(strict_types=1);

namespace Flex\Features\Shopping\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class OrderStatusHistory extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'shopping_order_status_history';

    protected $fillable = [
        'order_id',
        'status',
        'note',
        'changed_by',
    ];

    protected $casts = [
        'order_id' => 'integer',
        'created_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
