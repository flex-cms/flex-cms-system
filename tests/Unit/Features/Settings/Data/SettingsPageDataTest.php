<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Settings\Data;

use Flex\Features\Settings\Data\SettingsPageData;
use PHPUnit\Framework\TestCase;

final class SettingsPageDataTest extends TestCase
{
    public function testItExposesAndSerializesCurrentPageData(): void
    {
        $data = new SettingsPageData(
            group: 'general',
            storageGroup: 'general',
            title: 'General settings',
            label: 'General',
            description: 'Website configuration',
            values: ['site_name' => 'Flex CMS'],
            languages: ['bg' => 'Bulgarian'],
            timezones: ['Europe/Sofia' => 'Europe/Sofia'],
            dateFormats: ['d.m.Y' => '31.12.2025']
        );

        self::assertSame('general', $data->group);
        self::assertSame('general', $data->storageGroup);
        self::assertSame(['site_name' => 'Flex CMS'], $data->values);
        self::assertSame([
            'group' => 'general',
            'storageGroup' => 'general',
            'title' => 'General settings',
            'label' => 'General',
            'description' => 'Website configuration',
            'values' => ['site_name' => 'Flex CMS'],
            'languages' => ['bg' => 'Bulgarian'],
            'timezones' => ['Europe/Sofia' => 'Europe/Sofia'],
            'dateFormats' => ['d.m.Y' => '31.12.2025'],
        ], $data->toArray());
    }
}
