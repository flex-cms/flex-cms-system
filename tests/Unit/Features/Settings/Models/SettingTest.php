<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Settings\Models;

use Flex\Features\Settings\Models\Setting;
use JsonException;
use PHPUnit\Framework\TestCase;

final class SettingTest extends TestCase
{
    public function testItReturnsStringValue(): void
    {
        $setting = new Setting([
            'value' => 'Flex CMS',
            'type' => Setting::TYPE_STRING,
        ]);

        self::assertSame('Flex CMS', $setting->typedValue());
    }

    public function testItReturnsTrueBooleanValue(): void
    {
        $setting = new Setting([
            'value' => '1',
            'type' => Setting::TYPE_BOOLEAN,
        ]);

        self::assertTrue($setting->typedValue());
    }

    public function testItReturnsFalseBooleanValue(): void
    {
        $setting = new Setting([
            'value' => '0',
            'type' => Setting::TYPE_BOOLEAN,
        ]);

        self::assertFalse($setting->typedValue());
    }

    public function testItReturnsIntegerValue(): void
    {
        $setting = new Setting([
            'value' => '25',
            'type' => Setting::TYPE_INTEGER,
        ]);

        self::assertSame(25, $setting->typedValue());
    }

    public function testItReturnsDecodedJsonValue(): void
    {
        $setting = new Setting([
            'value' => '{"enabled":true,"roles":["admin","editor"]}',
            'type' => Setting::TYPE_JSON,
        ]);

        self::assertSame(
            [
                'enabled' => true,
                'roles' => ['admin', 'editor'],
            ],
            $setting->typedValue()
        );
    }

    public function testItReturnsNullWithoutCasting(): void
    {
        $setting = new Setting([
            'value' => null,
            'type' => Setting::TYPE_BOOLEAN,
        ]);

        self::assertNull($setting->typedValue());
    }

    public function testItDetectsValueTypes(): void
    {
        self::assertSame(
            Setting::TYPE_BOOLEAN,
            Setting::detectType(true)
        );

        self::assertSame(
            Setting::TYPE_INTEGER,
            Setting::detectType(42)
        );

        self::assertSame(
            Setting::TYPE_JSON,
            Setting::detectType(['enabled' => true])
        );

        self::assertSame(
            Setting::TYPE_JSON,
            Setting::detectType((object) ['enabled' => true])
        );

        self::assertSame(
            Setting::TYPE_STRING,
            Setting::detectType('Flex CMS')
        );

        self::assertSame(
            Setting::TYPE_STRING,
            Setting::detectType(null)
        );
    }

    public function testItSerializesBooleanValues(): void
    {
        self::assertSame('1', Setting::serializeValue(true));
        self::assertSame('0', Setting::serializeValue(false));
    }

    public function testItSerializesArrayAsJson(): void
    {
        self::assertSame(
            '{"name":"Flex CMS","enabled":true}',
            Setting::serializeValue([
                'name' => 'Flex CMS',
                'enabled' => true,
            ])
        );
    }

    public function testItPreservesScalarValues(): void
    {
        self::assertSame(
            'Flex CMS',
            Setting::serializeValue('Flex CMS')
        );

        self::assertSame(42, Setting::serializeValue(42));
        self::assertNull(Setting::serializeValue(null));
    }

    public function testItThrowsExceptionForInvalidJson(): void
    {
        $setting = new Setting([
            'value' => '{invalid-json}',
            'type' => Setting::TYPE_JSON,
        ]);

        $this->expectException(JsonException::class);

        $setting->typedValue();
    }
}
