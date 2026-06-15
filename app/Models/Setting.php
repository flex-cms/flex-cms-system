<?php

namespace Flex\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'group',
        'type',
        'options'
    ];

    protected $casts = [
        'options' => AsArrayObject::class,
    ];

    public static function getSetting(string $jsonKey, string $nestedKey, $default = null)
    {
        $data = self::get($jsonKey, []);
        return data_get($data, $nestedKey, $default);
    }

    public static function get(string $key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        if (!$setting) {
            return $default;
        }

        $value = self::castValue($setting->value, $setting->type);

        if ($setting->type === 'json' && $value === null) {
            return is_string($default) ? json_decode($default, true) : $default;
        }

        return $value;
    }

    private static function castValue($value, $type)
    {
        if ($value === null)
            return null;

        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'json' => is_string($value) ? json_decode($value, true) : $value,
            default => $value,
        };
    }
}
