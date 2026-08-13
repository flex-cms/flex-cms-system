<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Pages\Data;

use Flex\Features\Pages\Data\PageFieldData;
use Flex\Features\Pages\Data\PageFieldType;
use PHPUnit\Framework\TestCase;

final class PageFieldDataTest extends TestCase
{
    public function testItExposesAllSupportedFieldTypes(): void
    {
        self::assertSame([
            'text' => 'Кратък текст',
            'textarea' => 'Дълъг текст',
            'rich_text' => 'Визуален редактор',
            'image' => 'Изображение',
            'gallery' => 'Галерия',
        ], PageFieldType::options());
    }

    public function testItMapsPublicContractToPersistenceColumns(): void
    {
        $data = new PageFieldData(
            type: PageFieldType::Text,
            label: 'Заглавие',
            key: 'title',
            group: 'general',
            order: 10,
            hint: 'Основно заглавие',
            settings: ['maxlength' => 100]
        );

        self::assertSame('title', $data->toArray()['key']);
        self::assertSame('general', $data->toArray()['group']);
        self::assertSame(10, $data->toArray()['order']);
        self::assertSame([
            'type' => 'text',
            'label' => 'Заглавие',
            'field_key' => 'title',
            'field_group' => 'general',
            'position' => 10,
            'hint' => 'Основно заглавие',
            'settings' => ['maxlength' => 100],
        ], $data->toPersistenceArray());
    }
}
