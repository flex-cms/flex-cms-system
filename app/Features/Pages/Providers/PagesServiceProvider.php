<?php

declare(strict_types=1);

namespace Flex\Features\Pages\Providers;

use Flex\Core\Container\Container;
use Flex\Core\Features\Contracts\FeatureServiceProviderInterface;
use Flex\Features\Pages\Navigation\PagesNavigation;
use Flex\Features\Pages\Repositories\EloquentPageElementRepository;
use Flex\Features\Pages\Repositories\EloquentPageOptionRepository;
use Flex\Features\Pages\Repositories\EloquentPageRepository;
use Flex\Features\Pages\Repositories\PageElementRepositoryInterface;
use Flex\Features\Pages\Repositories\PageOptionRepositoryInterface;
use Flex\Features\Pages\Repositories\PageRepositoryInterface;
use Flex\Features\Pages\Services\PageElementService;
use Flex\Features\Pages\Services\PageOptionService;
use Flex\Features\Pages\Services\PageService;
use Flex\Features\Pages\Services\PageTreeService;

final class PagesServiceProvider implements
    FeatureServiceProviderInterface
{
    public function register(Container $container): void
    {
        $container->singleton(
            PageRepositoryInterface::class,
            EloquentPageRepository::class
        );

        $container->singleton(
            PageOptionRepositoryInterface::class,
            EloquentPageOptionRepository::class
        );

        $container->singleton(
            PageElementRepositoryInterface::class,
            EloquentPageElementRepository::class
        );

        $container->singleton(PageTreeService::class);
        $container->singleton(PageService::class);
        $container->singleton(PageOptionService::class);
        $container->singleton(PageElementService::class);
        $container->singleton(PagesNavigation::class);

        $container
            ->make(PagesNavigation::class)
            ->register();
    }
}
