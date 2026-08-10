<?php

declare(strict_types=1);

namespace Flex\Features\Settings\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;

final class Setting extends Model
{
    public const CORE_GROUP = 'general';
    public const TYPE_STRING = 'string';
    public const TYPE_BOOLEAN = 'boolean';
    public const TYPE_INTEGER = 'integer';
    public const TYPE_JSON = 'json';

    protected $table = 'settings';

    protected $fillable = [
        'key',
        'value',
        'group',
        'type',
        'options',
    ];

    protected $casts = [
        'options' => AsArrayObject::class,
    ];

    public function typedValue(): mixed
    {
        if ($this->value === null) {
            return null;
        }

        return match ($this->type) {
            self::TYPE_BOOLEAN => filter_var(
                $this->value,
                FILTER_VALIDATE_BOOLEAN
            ),

            self::TYPE_INTEGER => (int) $this->value,

            self::TYPE_JSON => $this->decodeJsonValue($this->value),

            default => $this->value,
        };
    }

    public static function detectType(mixed $value): string
    {
        return match (true) {
            is_bool($value) => self::TYPE_BOOLEAN,
            is_int($value) => self::TYPE_INTEGER,
            is_array($value), is_object($value) => self::TYPE_JSON,
            default => self::TYPE_STRING,
        };
    }

    public static function serializeValue(mixed $value): mixed
    {
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_array($value) || is_object($value)) {
            return json_encode(
                $value,
                JSON_THROW_ON_ERROR
                | JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
            );
        }

        return $value;
    }

    private function decodeJsonValue(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        return json_decode(
            $value,
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }
}
