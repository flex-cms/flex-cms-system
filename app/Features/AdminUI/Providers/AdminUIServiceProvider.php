<?php

declare(strict_types=1);

namespace Flex\Features\AdminUI\Providers;

use Flex\Core\Container\Container;
use Flex\Core\Features\Contracts\FeatureServiceProviderInterface;
use Flex\Features\AdminUI\Configuration\AdminUIConfig;
use Flex\Features\AdminUI\Services\AdminUIAssets;
use Flex\Features\AdminUI\Services\AdminUIRenderer;

final class AdminUIServiceProvider implements
    FeatureServiceProviderInterface
{
    public function register(
        Container $container
    ): void {
        $container->singleton(
            AdminUIConfig::class
        );

        $container->singleton(
            AdminUIAssets::class
        );
        
        $container->singleton(
            AdminUIRenderer::class
        );
    }
}
