<?php

declare(strict_types=1);

namespace Tests\Unit\Features\AdminUI\Navigation;

use Flex\Features\AdminUI\Navigation\NavigationItem;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class NavigationItemTest extends TestCase
{
    public function testItCreatesNavigationItem(): void
    {
        $item = NavigationItem::make(
            'dashboard',
            'Табло',
            '/admin'
        )
            ->icon('fa-solid fa-gauge-high')
            ->priority(10)
            ->badge(5)
            ->turbo()
            ->exact();

        self::assertSame(
            [
                'id' => 'dashboard',
                'label' => 'Табло',
                'url' => '/admin',
                'icon' =>
                    'fa-solid fa-gauge-high',
                'priority' => 10,
                'badge' => 5,
                'turbo' => true,
                'exact' => true,
                'target' => '_self',
                'activePatterns' => [],
                'children' => [],
            ],
            $item->toArray()
        );
    }

    public function testFluentMethodsDoNotModifyOriginalItem(): void
    {
        $original = NavigationItem::make(
            'dashboard',
            'Табло',
            '/admin'
        );

        $modified = $original
            ->priority(10)
            ->turbo();

        self::assertSame(
            100,
            $original->priorityValue()
        );

        self::assertFalse(
            $original->toArray()['turbo']
        );

        self::assertSame(
            10,
            $modified->priorityValue()
        );

        self::assertTrue(
            $modified->toArray()['turbo']
        );
    }

    public function testItSortsChildrenByPriorityAndLabel(): void
    {
        $item = NavigationItem::make(
            'settings',
            'Настройки'
        )->children([
            NavigationItem::make(
                'settings.media',
                'Файлове',
                '/admin/settings/media'
            )->priority(20),

            NavigationItem::make(
                'settings.mail',
                'Имейл',
                '/admin/settings/mail'
            )->priority(10),

            NavigationItem::make(
                'settings.general',
                'Общи',
                '/admin/settings/general'
            )->priority(10),
        ]);

        $children = $item->toArray()['children'];

        self::assertSame(
            [
                'settings.mail',
                'settings.general',
                'settings.media',
            ],
            array_column($children, 'id')
        );
    }

    public function testItAddsChildWithoutChangingOriginal(): void
    {
        $parent = NavigationItem::make(
            'settings',
            'Настройки'
        );

        $modified = $parent->addChild(
            NavigationItem::make(
                'settings.general',
                'Общи настройки',
                '/admin/settings/general'
            )
        );

        self::assertCount(
            0,
            $parent->childItems()
        );

        self::assertCount(
            1,
            $modified->childItems()
        );
    }

    public function testItFiltersInvisibleChildren(): void
    {
        $item = NavigationItem::make(
            'settings',
            'Настройки'
        )->children([
            NavigationItem::make(
                'settings.visible',
                'Видим',
                '/visible'
            )->visibleWhen(
                static fn (
                    mixed $context
                ): bool =>
                    $context['allowed'] === true
            ),

            NavigationItem::make(
                'settings.hidden',
                'Скрит',
                '/hidden'
            )->visibleWhen(
                static fn (): bool => false
            ),
        ]);

        $children = $item->toArray([
            'allowed' => true,
        ])['children'];

        self::assertSame(
            ['settings.visible'],
            array_column($children, 'id')
        );
    }

    public function testItSupportsActivePatternsAndTarget(): void
    {
        $item = NavigationItem::make(
            'documentation',
            'Документация',
            'https://example.com/docs'
        )
            ->activeWhen(
                '/docs',
                '/documentation/*',
                '/docs'
            )
            ->target('_blank');

        self::assertSame(
            [
                '/docs',
                '/documentation/*',
            ],
            $item->toArray()['activePatterns']
        );

        self::assertSame(
            '_blank',
            $item->toArray()['target']
        );
    }

    public function testItRejectsInvalidId(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Invalid navigation item id [invalid id].'
        );

        NavigationItem::make(
            'invalid id',
            'Invalid'
        );
    }

    public function testItRejectsEmptyLabel(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        NavigationItem::make(
            'dashboard',
            ' '
        );
    }

    public function testItRejectsInvalidTarget(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        NavigationItem::make(
            'dashboard',
            'Табло'
        )->target('popup');
    }

    public function testChildrenMustBeNavigationItems(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        NavigationItem::make(
            'settings',
            'Настройки'
        )->children([
            'invalid child',
        ]);
    }
}
