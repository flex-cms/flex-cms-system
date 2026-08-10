<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Dashboard\Navigation;

use Flex\Features\AdminUI\Navigation\DefaultAdminNavigation;
use Flex\Features\AdminUI\Navigation\SidebarRegistry;
use Flex\Features\Dashboard\Navigation\DashboardNavigation;
use LogicException;
use PHPUnit\Framework\TestCase;

final class DashboardNavigationTest extends TestCase
{
    public function testItRegistersTheDashboardNavigationItem(): void
    {
        $registry = new SidebarRegistry();

        (new DefaultAdminNavigation($registry))
            ->register();

        $navigation = new DashboardNavigation(
            $registry
        );

        $item = $navigation->register();
        $itemData = $item->toArray();

        self::assertSame(
            'dashboard',
            $itemData['id']
        );

        self::assertSame(
            'Табло',
            $itemData['label']
        );

        self::assertSame(
            '/admin/dashboard-preview',
            $itemData['url']
        );

        self::assertSame(
            'fa-solid fa-gauge-high',
            $itemData['icon']
        );

        self::assertSame(
            1,
            $itemData['priority']
        );

        self::assertTrue(
            $itemData['exact']
        );
    }

    public function testItAddsTheItemToTheDefaultSidebar(): void
    {
        $registry = new SidebarRegistry();

        (new DefaultAdminNavigation($registry))
            ->register();

        (new DashboardNavigation($registry))
            ->register();

        $sidebar = $registry->sidebar(
            SidebarRegistry::DEFAULT_SIDEBAR
        );

        self::assertTrue(
            $sidebar->has('dashboard')
        );

        self::assertSame(
            'dashboard',
            $sidebar->find('dashboard')
                ?->toArray()['id']
        );
    }

    public function testItDoesNotDuplicateTheDashboardItem(): void
    {
        $registry = new SidebarRegistry();

        (new DefaultAdminNavigation($registry))
            ->register();

        $navigation = new DashboardNavigation(
            $registry
        );

        $firstItem = $navigation->register();
        $secondItem = $navigation->register();

        self::assertSame(
            $firstItem,
            $secondItem
        );

        $sidebarData = $registry
            ->sidebar(
                SidebarRegistry::DEFAULT_SIDEBAR
            )
            ->toArray();

        self::assertCount(
            1,
            $sidebarData['items']
        );
    }

    public function testItRequiresTheDefaultSidebar(): void
    {
        $registry = new SidebarRegistry();

        $navigation = new DashboardNavigation(
            $registry
        );

        $this->expectException(
            LogicException::class
        );

        $navigation->register();
    }
}
