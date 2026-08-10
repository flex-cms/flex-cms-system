<?php

declare(strict_types=1);

namespace Flex\Features\Dashboard\Providers;

use Flex\Core\Container\Container;
use Flex\Core\Features\Contracts\FeatureServiceProviderInterface;
use Flex\Features\Dashboard\Navigation\DashboardNavigation;
use Flex\Features\Dashboard\Repositories\DashboardRepositoryInterface;
use Flex\Features\Dashboard\Repositories\EloquentDashboardRepository;
use Flex\Features\Dashboard\Services\DashboardService;

final class DashboardServiceProvider implements
    FeatureServiceProviderInterface
{
    public function register(
        Container $container
    ): void {
        $container->singleton(
            DashboardRepositoryInterface::class,
            EloquentDashboardRepository::class
        );

        $container->singleton(
            DashboardService::class
        );

        $container->singleton(
            DashboardNavigation::class
        );

        $container
            ->make(DashboardNavigation::class)
            ->register();
    }
}
