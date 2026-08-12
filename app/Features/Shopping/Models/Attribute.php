<?php

declare(strict_types=1);

namespace Flex\Features\Shopping\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $type
 * @property int $sort_order
 * @property bool $is_active
 *
 * @property-read Collection<int, AttributeValue> $values
 */
final class Attribute extends Model
{
    protected $table = 'shopping_attributes';

    protected $fillable = [
        'name',
        'slug',
        'type',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function values(): HasMany
    {
        return $this->hasMany(
            AttributeValue::class,
            'attribute_id'
        )->orderBy('sort_order');
    }
}
