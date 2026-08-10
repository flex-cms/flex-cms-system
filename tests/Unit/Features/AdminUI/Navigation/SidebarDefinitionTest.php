<?php

declare(strict_types=1);

namespace Tests\Unit\Features\AdminUI\Navigation;

use Flex\Features\AdminUI\Navigation\NavigationItem;
use Flex\Features\AdminUI\Navigation\SidebarDefinition;
use Flex\Features\AdminUI\Navigation\SidebarPosition;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\TestCase;

final class SidebarDefinitionTest extends TestCase
{
    public function testItCreatesSidebar(): void
    {
        $sidebar = SidebarDefinition::make(
            'admin-primary',
            'Основна навигация'
        )
            ->position(
                SidebarPosition::Right
            )
            ->priority(10)
            ->collapsible(false);

        self::assertSame(
            'admin-primary',
            $sidebar->id()
        );

        self::assertSame(
            'Основна навигация',
            $sidebar->label()
        );

        self::assertSame(
            SidebarPosition::Right,
            $sidebar->positionValue()
        );

        self::assertSame(
            10,
            $sidebar->priorityValue()
        );

        self::assertFalse(
            $sidebar->isCollapsible()
        );
    }

    public function testItAddsAndSortsRootItems(): void
    {
        $sidebar = $this->sidebar();

        $sidebar
            ->add(
                NavigationItem::make(
                    'settings',
                    'Настройки'
                )->priority(20)
            )
            ->add(
                NavigationItem::make(
                    'users',
                    'Потребители'
                )->priority(10)
            )
            ->add(
                NavigationItem::make(
                    'dashboard',
                    'Табло'
                )->priority(10)
            );

        self::assertSame(
            [
                'users',
                'dashboard',
                'settings',
            ],
            array_map(
                static fn (
                    NavigationItem $item
                ): string => $item->id(),
                $sidebar->navigationItems()
            )
        );
    }

    public function testItAddsNestedItem(): void
    {
        $sidebar = $this->sidebar();

        $sidebar->add(
            NavigationItem::make(
                'settings',
                'Настройки'
            )->children([
                NavigationItem::make(
                    'settings.system',
                    'Системни'
                ),
            ])
        );

        $sidebar->addTo(
            'settings.system',
            NavigationItem::make(
                'settings.system.cache',
                'Кеш',
                '/admin/settings/cache'
            )
        );

        self::assertTrue(
            $sidebar->has(
                'settings.system.cache'
            )
        );

        self::assertSame(
            '/admin/settings/cache',
            $sidebar
                ->find(
                    'settings.system.cache'
                )
                ?->url()
        );
    }

    public function testItRejectsDuplicateItemId(): void
    {
        $sidebar = $this->sidebar();

        $sidebar->add(
            NavigationItem::make(
                'settings',
                'Настройки'
            )->children([
                NavigationItem::make(
                    'settings.general',
                    'Общи'
                ),
            ])
        );

        $this->expectException(
            LogicException::class
        );

        $this->expectExceptionMessage(
            'Navigation item [settings.general] '
            . 'is already registered'
        );

        $sidebar->add(
            NavigationItem::make(
                'settings.general',
                'Duplicate'
            )
        );
    }

    public function testItRejectsMissingParent(): void
    {
        $sidebar = $this->sidebar();

        $this->expectException(
            LogicException::class
        );

        $this->expectExceptionMessage(
            'Navigation parent [missing] '
            . 'was not found'
        );

        $sidebar->addTo(
            'missing',
            NavigationItem::make(
                'child',
                'Child'
            )
        );
    }

    public function testItRemovesRootItem(): void
    {
        $sidebar = $this->sidebar()
            ->add(
                NavigationItem::make(
                    'dashboard',
                    'Табло'
                )
            );

        $sidebar->remove('dashboard');

        self::assertFalse(
            $sidebar->has('dashboard')
        );
    }

    public function testItRemovesNestedItem(): void
    {
        $sidebar = $this->sidebar()
            ->add(
                NavigationItem::make(
                    'settings',
                    'Настройки'
                )->children([
                    NavigationItem::make(
                        'settings.general',
                        'Общи'
                    ),
                ])
            );

        $sidebar->remove(
            'settings.general'
        );

        self::assertFalse(
            $sidebar->has(
                'settings.general'
            )
        );

        self::assertTrue(
            $sidebar->has('settings')
        );
    }

    public function testItFiltersInvisibleRootItems(): void
    {
        $sidebar = $this->sidebar()
            ->add(
                NavigationItem::make(
                    'visible',
                    'Видим'
                )
            )
            ->add(
                NavigationItem::make(
                    'hidden',
                    'Скрит'
                )->visibleWhen(
                    static fn (): bool =>
                        false
                )
            );

        self::assertSame(
            ['visible'],
            array_column(
                $sidebar->toArray()['items'],
                'id'
            )
        );
    }

    public function testItConvertsSidebarToArray(): void
    {
        $sidebar = $this->sidebar()
            ->position('right')
            ->priority(5)
            ->add(
                NavigationItem::make(
                    'dashboard',
                    'Табло',
                    '/admin'
                )->turbo()
            );

        $data = $sidebar->toArray();

        self::assertSame(
            'admin-primary',
            $data['id']
        );

        self::assertSame(
            'right',
            $data['position']
        );

        self::assertSame(
            5,
            $data['priority']
        );

        self::assertTrue(
            $data['collapsible']
        );

        self::assertSame(
            'dashboard',
            $data['items'][0]['id']
        );
    }

    public function testItRejectsInvalidSidebarId(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        SidebarDefinition::make(
            'invalid sidebar',
            'Invalid'
        );
    }

    private function sidebar(): SidebarDefinition
    {
        return SidebarDefinition::make(
            'admin-primary',
            'Основна навигация'
        );
    }
}
