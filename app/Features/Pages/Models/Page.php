<?php

declare(strict_types=1);

namespace Flex\Features\Pages\Models;

use Flex\Features\Pages\Support\PagesTables;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $full_slug
 * @property int|null $parent_id
 * @property int $position
 * @property bool $is_active
 * @property-read Page|null $parent
 * @property-read Collection<int, Page> $children
 * @property-read Collection<int, PageOption> $pageOptions
 * @property-read Collection<int, PageElement> $elements
 * @property-read Collection<int, PageField> $fields
 */
final class Page extends Model
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

    public function getTable(): string
    {
        return PagesTables::pages();
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

    public function fields(): HasMany
    {
        return $this->hasMany(PageField::class, 'page_id')
            ->orderBy('field_group')
            ->orderBy('position')
            ->orderBy('id');
    }

    public function getOption(string $key, mixed $default = null): mixed
    {
        $option = $this->relationLoaded('pageOptions')
            ? $this->pageOptions->firstWhere('option_key', $key)
            : $this->pageOptions()
                ->where('option_key', $key)
                ->first();

        return $option instanceof PageOption
            ? $option->decodedValue()
            : $default;
    }

    /** @return array<string, mixed> */
    public function getOptions(): array
    {
        $options = $this->relationLoaded('pageOptions')
            ? $this->pageOptions
            : $this->pageOptions()->get();

        return $options
            ->mapWithKeys(static fn (PageOption $option): array => [
                $option->option_key => $option->decodedValue(),
            ])
            ->all();
    }

    public function hasOption(string $key): bool
    {
        if ($this->relationLoaded('pageOptions')) {
            return $this->pageOptions->contains('option_key', $key);
        }

        return $this->pageOptions()
            ->where('option_key', $key)
            ->exists();
    }

    public function getElement(
        string $type,
        ?PageElement $default = null
    ): ?PageElement {
        $element = $this->relationLoaded('elements')
            ? $this->elements->firstWhere('element_type', $type)
            : $this->elements()
                ->where('element_type', $type)
                ->first();

        return $element ?? $default;
    }

    /** @return Collection<int, PageElement> */
    public function getElementsByType(string $type): Collection
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
}
