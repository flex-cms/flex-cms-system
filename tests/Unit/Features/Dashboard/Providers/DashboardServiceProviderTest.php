<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Dashboard\Providers;

use Flex\Core\Container\Container;
use Flex\Features\Dashboard\Navigation\DashboardNavigation;
use Flex\Features\Dashboard\Providers\DashboardServiceProvider;
use Flex\Features\Dashboard\Services\DashboardService;
use PHPUnit\Framework\TestCase;
use Flex\Features\AdminUI\Navigation\DefaultAdminNavigation;
use Flex\Features\AdminUI\Navigation\SidebarRegistry;

final class DashboardServiceProviderTest extends TestCase
{
    public function testItRegistersDashboardServicesAsSingletons(): void
    {
        $container = new Container();

        $registry = new SidebarRegistry();

        (new DefaultAdminNavigation($registry))
            ->register();

        $container->instance(
            SidebarRegistry::class,
            $registry
        );

        (new DashboardServiceProvider())
            ->register($container);

        self::assertSame(
            $container->get(DashboardService::class),
            $container->get(DashboardService::class)
        );

        self::assertSame(
            $container->get(DashboardNavigation::class),
            $container->get(DashboardNavigation::class)
        );

        self::assertTrue(
            $registry
                ->sidebar(
                    SidebarRegistry::DEFAULT_SIDEBAR
                )
                ->has('dashboard')
        );
    }
}
