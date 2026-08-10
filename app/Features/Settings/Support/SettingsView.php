<?php

declare(strict_types=1);

namespace Flex\Features\Settings\Support;

final class SettingsView
{
    public static function value(
        array $values,
        string $key,
        mixed $default = null
    ): mixed {
        return array_key_exists($key, $values)
            ? $values[$key]
            : $default;
    }

    public static function escape(
        mixed $value
    ): string {
        return htmlspecialchars(
            (string) $value,
            ENT_QUOTES,
            'UTF-8'
        );
    }

    public static function checked(
        array $values,
        string $key,
        bool $default = false
    ): string {
        $value = self::value(
            $values,
            $key,
            $default
        );

        if (is_bool($value)) {
            return $value
                ? 'checked'
                : '';
        }

        return filter_var(
            $value,
            FILTER_VALIDATE_BOOLEAN
        )
            ? 'checked'
            : '';
    }
}
