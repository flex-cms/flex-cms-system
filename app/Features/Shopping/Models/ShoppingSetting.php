<?php

declare(strict_types=1);

namespace Flex\Features\Shopping\Models;

use Illuminate\Database\Eloquent\Model;

final class ShoppingSetting extends Model
{
    protected $table = 'shopping_settings';

    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
    ];

    public static function getValue(
        string $key,
        mixed $default = null,
        string $group = 'general'
    ): mixed {
        $setting = self::query()
            ->where('group', $group)
            ->where('key', $key)
            ->first();

        if ($setting === null) {
            return $default;
        }

        return $setting->castStoredValue();
    }

    public function castStoredValue(): mixed
    {
        return match ($this->type) {
            'integer' => (int) $this->value,
            'float', 'decimal' => (float) $this->value,
            'boolean' => filter_var(
                $this->value,
                FILTER_VALIDATE_BOOLEAN
            ),
            'json' => json_decode(
                (string) $this->value,
                true
            ),
            default => $this->value,
        };
    }
}
