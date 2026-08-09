<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Settings\Data;

use Flex\Features\Settings\Data\SettingsPageData;
use PHPUnit\Framework\TestCase;

final class SettingsPageDataTest extends TestCase
{
    public function testItExposesPageData(): void
    {
        $groups = [
            'general' => [
                'label' => 'Общи настройки',
                'url' => '/admin/settings/general',
            ],
        ];

        $settings = [
            'site_name' => 'Flex CMS',
            'debug_mode' => false,
        ];

        $languages = [
            'bg' => 'Български',
            'en' => 'English',
        ];

        $timezones = [
            'Europe/Sofia' => 'Sofia',
        ];

        $dateFormats = [
            'd.m.Y' => 'Ден.Месец.Година',
        ];

        $data = new SettingsPageData(
            title: 'Настройки: Общи настройки',
            currentGroup: 'general',
            definedGroups: $groups,
            settings: $settings,
            languages: $languages,
            timezones: $timezones,
            dateFormats: $dateFormats
        );

        self::assertSame(
            'Настройки: Общи настройки',
            $data->title
        );

        self::assertSame('general', $data->currentGroup);
        self::assertSame($groups, $data->definedGroups);
        self::assertSame($settings, $data->settings);
        self::assertSame($languages, $data->languages);
        self::assertSame($timezones, $data->timezones);
        self::assertSame($dateFormats, $data->dateFormats);
    }

    public function testItConvertsDataToViewArray(): void
    {
        $data = new SettingsPageData(
            title: 'Настройки: Общи настройки',
            currentGroup: 'general',
            definedGroups: [
                'general' => [
                    'label' => 'Общи настройки',
                    'url' => '/admin/settings/general',
                ],
            ],
            settings: [
                'site_name' => 'Flex CMS',
            ],
            languages: [
                'bg' => 'Български',
            ],
            timezones: [
                'Europe/Sofia' => 'Sofia',
            ],
            dateFormats: [
                'd.m.Y' => 'Ден.Месец.Година',
            ]
        );

        self::assertSame(
            [
                'title' => 'Настройки: Общи настройки',
                'currentGroup' => 'general',
                'group' => 'general',
                'definedGroups' => [
                    'general' => [
                        'label' => 'Общи настройки',
                        'url' => '/admin/settings/general',
                    ],
                ],
                'settings' => [
                    'site_name' => 'Flex CMS',
                ],
                'languages' => [
                    'bg' => 'Български',
                ],
                'timezones' => [
                    'Europe/Sofia' => 'Sofia',
                ],
                'dateFormats' => [
                    'd.m.Y' => 'Ден.Месец.Година',
                ],
            ],
            $data->toArray()
        );
    }
}