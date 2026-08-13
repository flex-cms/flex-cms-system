<?php

declare(strict_types=1);

namespace Flex\Features\Pages\Models;

use Flex\Features\Pages\Support\PagesTables;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $page_id
 * @property int|null $parent_id
 * @property string $element_type
 * @property int $position
 * @property \ArrayObject<string, mixed>|null $settings
 * @property-read Page $page
 * @property-read PageElement|null $parent
 * @property-read Collection<int, PageElement> $children
 */
final class PageElement extends Model
{
    protected $fillable = [
        'page_id',
        'parent_id',
        'element_type',
        'position',
        'settings',
    ];

    protected $casts = [
        'page_id' => 'integer',
        'parent_id' => 'integer',
        'position' => 'integer',
        'settings' => AsArrayObject::class,
    ];

    public function getTable(): string
    {
        return PagesTables::elements();
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'page_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
            ->orderBy('position');
    }
}
