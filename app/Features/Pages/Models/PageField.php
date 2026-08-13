<?php

declare(strict_types=1);

namespace Flex\Features\Pages\Models;

use Flex\Features\Pages\Data\PageFieldType;
use Flex\Features\Pages\Support\PagesTables;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $page_id
 * @property PageFieldType $type
 * @property string $label
 * @property string $field_key
 * @property string $field_group
 * @property int $position
 * @property string|null $hint
 * @property \ArrayObject<string, mixed>|null $settings
 * @property-read Page $page
 */
final class PageField extends Model
{
    protected $fillable = [
        'page_id',
        'type',
        'label',
        'field_key',
        'field_group',
        'position',
        'hint',
        'settings',
    ];

    protected $casts = [
        'page_id' => 'integer',
        'type' => PageFieldType::class,
        'position' => 'integer',
        'settings' => AsArrayObject::class,
    ];

    public function getTable(): string
    {
        return PagesTables::fields();
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'page_id');
    }

    public function fieldKey(): string
    {
        return $this->field_key;
    }

    public function getGroupName(): string
    {
        return $this->field_group;
    }

    public function getOrder(): int
    {
        return (int) $this->position;
    }
}
