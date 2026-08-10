<?php

declare(strict_types=1);

namespace Tests\Unit\Features\AdminUI\Navigation;

use Flex\Features\AdminUI\Navigation\NavigationItem;
use Flex\Features\AdminUI\Navigation\SidebarDefinition;
use Flex\Features\AdminUI\Navigation\SidebarPosition;
use Flex\Features\AdminUI\Navigation\SidebarRegistry;
use LogicException;
use PHPUnit\Framework\TestCase;

final class SidebarRegistryTest extends TestCase
{
    public function testItCreatesAndRetrievesASidebar(): void
    {
        $registry = new SidebarRegistry();

        $sidebar = $registry->create(
            SidebarRegistry::DEFAULT_SIDEBAR,
            'Administration',
            SidebarPosition::Left
        );

        self::assertSame(
            $sidebar,
            $registry->sidebar(SidebarRegistry::DEFAULT_SIDEBAR)
        );

        self::assertTrue(
            $registry->has(SidebarRegistry::DEFAULT_SIDEBAR)
        );
    }

    public function testItRegistersAnExistingSidebarDefinition(): void
    {
        $registry = new SidebarRegistry();

        $sidebar = SidebarDefinition::make(
            'secondary',
            'Secondary navigation'
        )->position(SidebarPosition::Right);

        $registry->register($sidebar);

        self::assertSame($sidebar, $registry->sidebar('secondary'));

        self::assertSame(
            SidebarPosition::Right->value,
            $registry->sidebar('secondary')->toArray()['position']
        );
    }

    public function testItRejectsDuplicateSidebarIdentifiers(): void
    {
        $registry = new SidebarRegistry();

        $registry->create('admin', 'Administration');

        $this->expectException(LogicException::class);

        $registry->create('admin', 'Duplicate administration');
    }

    public function testItThrowsWhenSidebarDoesNotExist(): void
    {
        $registry = new SidebarRegistry();

        $this->expectException(LogicException::class);

        $registry->sidebar('missing');
    }

    public function testItAddsNavigationItemsToASidebar(): void
    {
        $registry = new SidebarRegistry();

        $registry->create(
            SidebarRegistry::DEFAULT_SIDEBAR,
            'Administration'
        );

        $registry->add(
            NavigationItem::make(
                'dashboard',
                'Dashboard',
                '/admin/dashboard'
            )
        );

        $sidebar = $registry->sidebar(
            SidebarRegistry::DEFAULT_SIDEBAR
        );

        self::assertTrue($sidebar->has('dashboard'));

        $dashboard = $sidebar->find('dashboard');

        self::assertNotNull($dashboard);
        self::assertSame(
            '/admin/dashboard',
            $dashboard->toArray()['url']
        );
    }

    public function testItAddsNestedNavigationItems(): void
    {
        $registry = new SidebarRegistry();

        $registry->create(
            SidebarRegistry::DEFAULT_SIDEBAR,
            'Administration'
        );

        $registry->add(
            NavigationItem::make(
                'settings',
                'Settings',
                '/admin/settings'
            )
        );

        $registry->addTo(
            'settings',
            NavigationItem::make(
                'settings.general',
                'General',
                '/admin/settings/general'
            )
        );

        $settings = $registry
            ->sidebar(SidebarRegistry::DEFAULT_SIDEBAR)
            ->find('settings');

        self::assertNotNull($settings);

        $settingsData = $settings->toArray();

        self::assertCount(1, $settingsData['children']);
        self::assertSame(
            'settings.general',
            $settingsData['children'][0]['id']
        );
    }

    public function testItConfiguresASidebar(): void
    {
        $registry = new SidebarRegistry();

        $registry->create(
            SidebarRegistry::DEFAULT_SIDEBAR,
            'Administration'
        );

        $registry->configure(
            SidebarRegistry::DEFAULT_SIDEBAR,
            static function (SidebarDefinition $sidebar): void {
                $sidebar
                    ->position(SidebarPosition::Right)
                    ->priority(25)
                    ->collapsible(false);
            }
        );

        $sidebarData = $registry
            ->sidebar(SidebarRegistry::DEFAULT_SIDEBAR)
            ->toArray();

        self::assertSame(
            SidebarPosition::Right->value,
            $sidebarData['position']
        );

        self::assertSame(25, $sidebarData['priority']);
        self::assertFalse($sidebarData['collapsible']);
    }

    public function testItReturnsSidebarsOrderedByPriorityAndLabel(): void
    {
        $registry = new SidebarRegistry();

        $registry->create('third', 'Zulu')->priority(20);
        $registry->create('second', 'Beta')->priority(10);
        $registry->create('first', 'Alpha')->priority(10);

        $sidebars = $registry->all();

        self::assertSame(
            ['first', 'second', 'third'],
            array_map(
                static fn(SidebarDefinition $sidebar): string =>
                $sidebar->toArray()['id'],
                $sidebars
            )
        );
    }

    public function testItReturnsSidebarsForAGivenPosition(): void
    {
        $registry = new SidebarRegistry();

        $registry
            ->create('left', 'Left sidebar')
            ->position(SidebarPosition::Left);

        $registry
            ->create('right', 'Right sidebar')
            ->position(SidebarPosition::Right);

        $leftSidebars = $registry->positioned(
            SidebarPosition::Left
        );

        $rightSidebars = $registry->positioned(
            SidebarPosition::Right
        );

        self::assertCount(1, $leftSidebars);
        self::assertCount(1, $rightSidebars);

        self::assertSame(
            'left',
            $leftSidebars[0]->toArray()['id']
        );

        self::assertSame(
            'right',
            $rightSidebars[0]->toArray()['id']
        );
    }

    public function testItRemovesASidebar(): void
    {
        $registry = new SidebarRegistry();

        $registry->create('temporary', 'Temporary');

        self::assertTrue($registry->has('temporary'));

        $result = $registry->remove('temporary');

        self::assertSame($registry, $result);
        self::assertFalse($registry->has('temporary'));

        // Премахването на несъществуваща лента не трябва да хвърля грешка.
        self::assertSame(
            $registry,
            $registry->remove('temporary')
        );
    }

    public function testItSerializesOnlyVisibleNavigationItems(): void
    {
        $registry = new SidebarRegistry();

        $registry->create(
            SidebarRegistry::DEFAULT_SIDEBAR,
            'Administration'
        );

        $registry->add(
            NavigationItem::make(
                'visible',
                'Visible',
                '/visible'
            )->visibleWhen(
                    static fn(mixed $context): bool =>
                    $context === 'administrator'
                )
        );

        $registry->add(
            NavigationItem::make(
                'hidden',
                'Hidden',
                '/hidden'
            )->visibleWhen(
                    static fn(): bool => false
                )
        );

        $result = $registry->toArray('administrator');

        self::assertCount(1, $result);
        self::assertSame(
            ['visible'],
            array_column($result[0]['items'], 'id')
        );
    }
}
