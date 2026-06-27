<?php

namespace Flex\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'full_slug',
        'parent_id',
        'position',
        'is_active',
        'options'
    ];

    protected $casts = [
        'options' => AsArrayObject::class,
        'is_active' => 'boolean',
        'parent_id' => 'integer',
        'position' => 'integer',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Page::class, 'parent_id')
        ->orderBy('position');
    }

    public function hasImage(): bool
    {
        return !empty($this->options['featured_image']);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('position');
    }

    public static function getFlattenedTree(iterable $pages, ?int $parentId = null, int $level = 0): array
    {
        $result = [];
        foreach ($pages as $page) {
            if ($page->parent_id == $parentId) {
                $page->display_name = str_repeat('— ', $level) . $page->name;
                $page->level = $level;

                $result[] = $page;

                $result = array_merge($result, self::getFlattenedTree($pages, $page->id, $level + 1));
            }
        }
        return $result;
    }
}
