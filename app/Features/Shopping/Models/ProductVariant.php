<?php

declare(strict_types=1);

namespace Flex\Features\Shopping\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $product_id
 * @property string|null $sku
 * @property string|null $price
 * @property string|null $compare_price
 * @property string|null $cost_price
 * @property bool $manage_stock
 * @property int $stock_quantity
 * @property string $stock_status
 * @property string|null $weight
 * @property bool $is_active
 * @property int $sort_order
 *
 * @property-read Product $product
 * @property-read Collection<int, AttributeValue> $values
 */
final class ProductVariant extends Model
{
    use SoftDeletes;

    protected $table = 'shopping_product_variants';

    protected $fillable = [
        'product_id',
        'sku',
        'price',
        'compare_price',
        'cost_price',
        'manage_stock',
        'stock_quantity',
        'stock_status',
        'weight',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'manage_stock' => 'boolean',
        'stock_quantity' => 'integer',
        'weight' => 'decimal:3',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(
            Product::class,
            'product_id'
        );
    }

    public function values(): BelongsToMany
    {
        return $this->belongsToMany(
            AttributeValue::class,
            'shopping_variant_values',
            'variant_id',
            'attribute_value_id'
        );
    }
}
