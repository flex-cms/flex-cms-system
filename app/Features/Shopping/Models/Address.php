<?php

declare(strict_types=1);

namespace Flex\Features\Shopping\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Address extends Model
{
    protected $table = 'shopping_addresses';

    protected $fillable = [
        'customer_id',
        'type',
        'first_name',
        'last_name',
        'company',
        'country_code',
        'city',
        'postal_code',
        'address_line_1',
        'address_line_2',
        'phone',
        'is_default',
    ];

    protected $casts = [
        'customer_id' => 'integer',
        'is_default' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
