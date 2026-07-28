<?php

namespace Flex\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
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
    ];

    protected $casts = [
        'parent_id' => 'integer',
        'position' => 'integer',
        'is_active' => 'boolean',
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

    public function pageOptions(): HasMany
    {
        return $this->hasMany(PageOption::class, 'page_id');
    }

    public function elements(): HasMany
    {
        return $this->hasMany(PageElement::class, 'page_id')
            ->whereNull('parent_id')
            ->orderBy('position');
    }

    public function getOption(string $key, mixed $default = null): mixed
    {
        $option = $this->relationLoaded('pageOptions')
            ? $this->pageOptions->firstWhere('option_key', $key)
            : $this->pageOptions()
                ->where('option_key', $key)
                ->first();

        if (!$option) {
            return $default;
        }

        return $this->decodeOptionValue($option->option_value);
    }

    public function getOptions(): array
    {
        $options = $this->relationLoaded('pageOptions')
            ? $this->pageOptions
            : $this->pageOptions()->get();

        return $options
            ->mapWithKeys(fn(PageOption $option) => [
                $option->option_key => $this->decodeOptionValue(
                    $option->option_value
                ),
            ])
            ->toArray();
    }

    public function hasOption(string $key): bool
    {
        if ($this->relationLoaded('pageOptions')) {
            return $this->pageOptions->contains(
                'option_key',
                $key
            );
        }

        return $this->pageOptions()
            ->where('option_key', $key)
            ->exists();
    }

    public function getElement(
        string $type,
        mixed $default = null
    ): mixed {
        $element = $this->relationLoaded('elements')
            ? $this->elements->firstWhere('element_type', $type)
            : $this->elements()
                ->where('element_type', $type)
                ->first();

        return $element ?? $default;
    }

    public function getElementsByType(string $type)
    {
        if ($this->relationLoaded('elements')) {
            return $this->elements
                ->where('element_type', $type)
                ->values();
        }

        return $this->elements()
            ->where('element_type', $type)
            ->get();
    }

    protected function decodeOptionValue(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        $decoded = json_decode($value, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        return $value;
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('position');
    }

    public function getElementSettings(
        string $type,
        array $default = []
    ): array {
        $element = $this->getElement($type);

        if (!$element) {
            return $default;
        }

        if ($element->settings instanceof \ArrayObject) {
            return $element->settings->getArrayCopy();
        }

        return is_array($element->settings)
            ? $element->settings
            : $default;
    }

    public static function getFlattenedTree(
        iterable $pages,
        ?int $parentId = null,
        int $level = 0
    ): array {
        $result = [];

        foreach ($pages as $page) {
            if ($page->parent_id == $parentId) {
                $page->display_name =
                    str_repeat('— ', $level) . $page->name;

                $page->level = $level;

                $result[] = $page;

                $result = array_merge(
                    $result,
                    self::getFlattenedTree(
                        $pages,
                        $page->id,
                        $level + 1
                    )
                );
            }
        }

        return $result;
    }
}