<?php

declare(strict_types=1);

namespace Flex\Features\Settings\Support;

use DateTimeZone;

final class SettingsOptions
{
    public static function languages(): array
    {
        return [
            'bg' => 'Български',
            'en' => 'English',
        ];
    }

    public static function dateFormats(): array
    {
        return [
            'd.m.Y' => '31.12.2026',
            'd/m/Y' => '31/12/2026',
            'Y-m-d' => '2026-12-31',
            'm/d/Y' => '12/31/2026',
        ];
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
}
