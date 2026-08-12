<?php

declare(strict_types=1);

namespace Flex\Features\Settings\Support;

use DateTimeZone;
use JsonException;
use RuntimeException;

final class SettingsOptions
{
    private const RESOURCE_FILE =
        __DIR__ . '/../Resources/settings.json';

    private static ?array $configuration = null;

    public static function languages(): array
    {
        return self::stringMap('languages');
    }

    public static function dateFormats(): array
    {
        return self::stringMap('date_formats');
    }

    public static function timezones(): array
    {
        $timezones = [];

        foreach (
            DateTimeZone::listIdentifiers()
            as $timezone
        ) {
            $timezones[$timezone] = str_replace(
                '_',
                ' ',
                $timezone
            );
        }

        return $timezones;
    }

    public static function defaultLocale(): string
    {
        return self::defaultValue(
            'locale',
            'bg'
        );
    }

    public static function defaultTimezone(): string
    {
        return self::defaultValue(
            'timezone',
            'Europe/Sofia'
        );
    }

    public static function defaultDateFormat(): string
    {
        return self::defaultValue(
            'date_format',
            'd.m.Y'
        );
    }

    public static function defaultTimeFormat(): string
    {
        return self::defaultValue(
            'time_format',
            'H:i'
        );
    }

    public static function hasLanguage(string $language): bool
    {
        return array_key_exists(
            $language,
            self::languages()
        );
    }

    public static function hasDateFormat(string $format): bool
    {
        return array_key_exists(
            $format,
            self::dateFormats()
        );
    }

    public static function hasTimezone(string $timezone): bool
    {
        return in_array(
            $timezone,
            DateTimeZone::listIdentifiers(),
            true
        );
    }

    private static function defaultValue(
        string $key,
        string $fallback
    ): string {
        $defaults = self::configuration()['defaults']
            ?? [];

        $value = $defaults[$key] ?? $fallback;

        return is_string($value)
            && $value !== ''
                ? $value
                : $fallback;
    }

    private static function stringMap(string $key): array
    {
        $values = self::configuration()[$key]
            ?? null;

        if (!is_array($values)) {
            throw new RuntimeException(
                sprintf(
                    'Settings source [%s] is not defined.',
                    $key
                )
            );
        }

        $result = [];

        foreach ($values as $value => $label) {
            if (!is_string($label)) {
                continue;
            }

            $result[(string) $value] = $label;
        }

        return $result;
    }

    private static function configuration(): array
    {
        if (self::$configuration !== null) {
            return self::$configuration;
        }

        $contents = file_get_contents(
            self::RESOURCE_FILE
        );

        if ($contents === false) {
            throw new RuntimeException(
                'Settings configuration could not be read.'
            );
        }

        try {
            $configuration = json_decode(
                $contents,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $exception) {
            throw new RuntimeException(
                'Settings configuration contains invalid JSON.',
                previous: $exception
            );
        }

        if (!is_array($configuration)) {
            throw new RuntimeException(
                'Settings configuration must contain a JSON object.'
            );
        }

        self::$configuration = $configuration;

        return self::$configuration;
    }
}
