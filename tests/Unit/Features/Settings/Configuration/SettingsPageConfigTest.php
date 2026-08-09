<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Settings\Configuration;

use Flex\Features\Settings\Configuration\SettingsPageConfig;
use PHPUnit\Framework\TestCase;

final class SettingsPageConfigTest extends TestCase
{
    private SettingsPageConfig $config;

    protected function setUp(): void
    {
        parent::setUp();

        $configuration = [
            'languages' => [
                'bg' => 'Български',
                'en' => 'English',
                'de' => 'Deutsch',
                'fr' => 'Français',
            ],
            'date_formats' => [
                'd.m.Y' => 'Ден.Месец.Година (31.12.2025)',
                'd/m/Y' => 'Ден/Месец/Година (31/12/2025)',
            ],
            'settings_options.settings_page_groups' => [
                'general' => [
                    'label' => 'Общи настройки',
                    'url' => '/admin/settings/general',
                ],
                'mail' => [
                    'label' => 'Имейл сървър',
                    'url' => '/admin/settings/mail',
                ],
                'media' => [
                    'label' => 'Файлове',
                    'url' => '/admin/settings/media',
                ],
            ],
        ];

        $this->config = new SettingsPageConfig(
            static fn(string $path, mixed $default = null): mixed =>
            $configuration[$path] ?? $default
        );
    }

    public function testItReturnsAllSettingsGroups(): void
    {
        $groups = $this->config->groups();

        self::assertArrayHasKey('general', $groups);
        self::assertArrayHasKey('mail', $groups);
        self::assertArrayHasKey('media', $groups);
    }

    public function testItChecksWhetherGroupExists(): void
    {
        self::assertTrue($this->config->hasGroup('general'));
        self::assertTrue($this->config->hasGroup('mail'));
        self::assertTrue($this->config->hasGroup('media'));

        self::assertFalse($this->config->hasGroup('unknown'));
    }

    public function testItReturnsGroupConfiguration(): void
    {
        self::assertSame(
            [
                'label' => 'Общи настройки',
                'url' => '/admin/settings/general',
            ],
            $this->config->group('general')
        );

        self::assertNull($this->config->group('unknown'));
    }

    public function testItReturnsGroupLabels(): void
    {
        self::assertSame(
            'Общи настройки',
            $this->config->label('general')
        );

        self::assertSame(
            'Имейл сървър',
            $this->config->label('mail')
        );

        self::assertSame(
            'Файлове',
            $this->config->label('media')
        );

        self::assertNull($this->config->label('unknown'));
    }

    public function testItReturnsGroupUrl(): void
    {
        self::assertSame(
            '/admin/settings/general',
            $this->config->url('general')
        );

        self::assertNull($this->config->url('unknown'));
    }

    public function testItReturnsLanguages(): void
    {
        $languages = $this->config->languages();

        self::assertSame('Български', $languages['bg']);
        self::assertSame('English', $languages['en']);
    }

    public function testItReturnsDateFormats(): void
    {
        $formats = $this->config->dateFormats();

        self::assertArrayHasKey('d.m.Y', $formats);
        self::assertSame(
            'Ден.Месец.Година (31.12.2025)',
            $formats['d.m.Y']
        );
    }

    public function testItReturnsSortedTimezones(): void
    {
        $timezones = $this->config->timezones();

        self::assertArrayHasKey('Europe/Sofia', $timezones);
        self::assertSame('Sofia', $timezones['Europe/Sofia']);

        $sortedTimezones = $timezones;
        asort($sortedTimezones, SORT_NATURAL | SORT_FLAG_CASE);

        self::assertSame($sortedTimezones, $timezones);
    }
}
