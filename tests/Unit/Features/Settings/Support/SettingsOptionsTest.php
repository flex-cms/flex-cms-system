<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Settings\Support;

use Flex\Features\Settings\Support\SettingsOptions;
use PHPUnit\Framework\TestCase;

final class SettingsOptionsTest extends TestCase
{
    public function testItLoadsLanguagesFromFeatureResource(): void
    {
        $languages = SettingsOptions::languages();

        self::assertArrayHasKey('bg', $languages);
        self::assertSame('English', $languages['en']);
        self::assertTrue(SettingsOptions::hasLanguage('bg'));
        self::assertFalse(SettingsOptions::hasLanguage('unknown'));
    }

    public function testItLoadsDateFormatsFromFeatureResource(): void
    {
        $formats = SettingsOptions::dateFormats();

        self::assertArrayHasKey('d.m.Y', $formats);
        self::assertTrue(SettingsOptions::hasDateFormat('d.m.Y'));
        self::assertFalse(SettingsOptions::hasDateFormat('invalid'));
    }

    public function testItProvidesRuntimeDefaults(): void
    {
        self::assertSame('bg', SettingsOptions::defaultLocale());
        self::assertSame('Europe/Sofia', SettingsOptions::defaultTimezone());
        self::assertSame('d.m.Y', SettingsOptions::defaultDateFormat());
        self::assertSame('H:i', SettingsOptions::defaultTimeFormat());
    }

    public function testItReturnsKnownTimezones(): void
    {
        $timezones = SettingsOptions::timezones();

        self::assertSame('Europe/Sofia', $timezones['Europe/Sofia']);
        self::assertTrue(SettingsOptions::hasTimezone('Europe/Sofia'));
        self::assertFalse(SettingsOptions::hasTimezone('Unknown/Timezone'));
    }
}
