<?php

declare(strict_types=1);

namespace Tests\Unit\Features\AdminUI\Navigation;

use Flex\Features\AdminUI\Navigation\DefaultAdminNavigation;
use Flex\Features\AdminUI\Navigation\SidebarPosition;
use Flex\Features\AdminUI\Navigation\SidebarRegistry;
use PHPUnit\Framework\TestCase;

final class DefaultAdminNavigationTest extends TestCase
{
    public function testItRegistersTheDefaultAdminSidebar(): void
    {
        $registry = new SidebarRegistry();

        $navigation = new DefaultAdminNavigation(
            $registry
        );

        $sidebar = $navigation->register();
        $sidebarData = $sidebar->toArray();

        self::assertTrue(
            $registry->has(
                SidebarRegistry::DEFAULT_SIDEBAR
            )
        );

        self::assertSame(
            SidebarRegistry::DEFAULT_SIDEBAR,
            $sidebarData['id']
        );

        self::assertSame(
            'Administration',
            $sidebarData['label']
        );

        self::assertSame(
            SidebarPosition::Left->value,
            $sidebarData['position']
        );

        self::assertSame(10, $sidebarData['priority']);
        self::assertTrue($sidebarData['collapsible']);
        self::assertSame([], $sidebarData['items']);
    }

    public function testItDoesNotDuplicateTheDefaultSidebar(): void
    {
        $registry = new SidebarRegistry();

        $navigation = new DefaultAdminNavigation(
            $registry
        );

        $firstSidebar = $navigation->register();
        $secondSidebar = $navigation->register();

        self::assertSame(
            $firstSidebar,
            $secondSidebar
        );

        self::assertCount(1, $registry->all());
    }

    public function testItReturnsAnExistingDefaultSidebar(): void
    {
        $registry = new SidebarRegistry();

        $existingSidebar = $registry->create(
            SidebarRegistry::DEFAULT_SIDEBAR,
            'Custom administration',
            SidebarPosition::Right
        );

        $navigation = new DefaultAdminNavigation(
            $registry
        );

        $registeredSidebar = $navigation->register();

        self::assertSame(
            $existingSidebar,
            $registeredSidebar
        );

        self::assertSame(
            'Custom administration',
            $registeredSidebar->toArray()['label']
        );

        self::assertSame(
            SidebarPosition::Right->value,
            $registeredSidebar->toArray()['position']
        );
    }
}
