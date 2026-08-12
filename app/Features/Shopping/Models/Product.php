<?php

declare(strict_types=1);

namespace Flex\Features\Shopping\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $sku
 * @property string|null $short_description
 * @property string|null $description
 * @property string $price
 * @property string|null $compare_price
 * @property string|null $cost_price
 * @property bool $manage_stock
 * @property int $stock_quantity
 * @property string $stock_status
 * @property string|null $weight
 * @property string|null $length
 * @property string|null $width
 * @property string|null $height
 * @property string $status
 * @property bool $is_featured
 * @property string|null $meta_title
 * @property string|null $meta_description
 *
 * @property-read Collection<int, Category> $categories
 * @property-read Collection<int, ProductImage> $images
 * @property-read ProductImage|null $primaryImage
 * @property-read Collection<int, ProductVariant> $variants
 */
final class Product extends Model
{
    use SoftDeletes;

    protected $table = 'shopping_products';

    protected $fillable = [
        'name',
        'slug',
        'sku',
        'short_description',
        'description',
        'price',
        'compare_price',
        'cost_price',
        'manage_stock',
        'stock_quantity',
        'stock_status',
        'weight',
        'length',
        'width',
        'height',
        'status',
        'is_featured',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'manage_stock' => 'boolean',
        'stock_quantity' => 'integer',
        'weight' => 'decimal:3',
        'length' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'is_featured' => 'boolean',
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            Category::class,
            'shopping_product_categories',
            'product_id',
            'category_id'
        );
    }

    public function images(): HasMany
    {
        return $this->hasMany(
            ProductImage::class,
            'product_id'
        )->orderBy('sort_order');
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(
            ProductImage::class,
            'product_id'
        )->where('is_primary', true);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(
            ProductVariant::class,
            'product_id'
        )->orderBy('sort_order');
    }
}
