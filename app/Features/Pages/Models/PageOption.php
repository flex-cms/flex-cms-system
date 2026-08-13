<?php

declare(strict_types=1);

namespace Flex\Features\Pages\Models;

use Flex\Features\Pages\Support\PagesTables;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $page_id
 * @property string $option_key
 * @property string|null $option_value
 * @property-read Page $page
 */
final class PageOption extends Model
{
    protected $fillable = [
        'page_id',
        'option_key',
        'option_value',
    ];

    protected $casts = [
        'page_id' => 'integer',
    ];

    public function getTable(): string
    {
        return PagesTables::options();
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'page_id');
    }

    public function decodedValue(): mixed
    {
        if ($this->option_value === null) {
            return null;
        }

        $decoded = json_decode($this->option_value, true);

        return json_last_error() === JSON_ERROR_NONE
            ? $decoded
            : $this->option_value;
    }
}
