<?php

declare(strict_types=1);

namespace Flex\Features\Shopping\Providers;

use Flex\Core\Container\Container;
use Flex\Core\Features\Contracts\FeatureServiceProviderInterface;
use Flex\Features\Shopping\Navigation\ShoppingNavigation;
use Flex\Features\Shopping\Repositories\CategoryRepositoryInterface;
use Flex\Features\Shopping\Repositories\EloquentCategoryRepository;
use Flex\Features\Shopping\Services\CategoryService;

final class ShoppingServiceProvider implements
    FeatureServiceProviderInterface
{
    public function register(
        Container $container
    ): void {
        $container->singleton(
            CategoryRepositoryInterface::class,
            EloquentCategoryRepository::class
        );

        $container->singleton(
            CategoryService::class
        );

        $container->singleton(
            ShoppingNavigation::class
        );

        $container
            ->make(ShoppingNavigation::class)
            ->register();
    }
}
