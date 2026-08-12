<?php

declare(strict_types=1);

namespace Flex\Features\Shopping\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property int $attribute_id
 * @property string $value
 * @property string $slug
 * @property int $sort_order
 *
 * @property-read Attribute $attribute
 * @property-read Collection<int, ProductVariant> $variants
 */
final class AttributeValue extends Model
{
    protected $table = 'shopping_attribute_values';

    protected $fillable = [
        'attribute_id',
        'value',
        'slug',
        'sort_order',
    ];

    protected $casts = [
        'attribute_id' => 'integer',
        'sort_order' => 'integer',
    ];

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(
            Attribute::class,
            'attribute_id'
        );
    }

    public function variants(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductVariant::class,
            'shopping_variant_values',
            'attribute_value_id',
            'variant_id'
        );
    }
}
