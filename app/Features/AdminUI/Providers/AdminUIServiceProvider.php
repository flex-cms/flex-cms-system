<?php

declare(strict_types=1);

namespace Flex\Features\AdminUI\Providers;

use Flex\Core\Assets\AdminAssetRegistry;
use Flex\Core\Assets\ViteAssetResolver;
use Flex\Core\Container\Container;
use Flex\Core\Features\Contracts\FeatureServiceProviderInterface;
use Flex\Features\AdminUI\Configuration\AdminUIConfig;
use Flex\Features\AdminUI\Navigation\SidebarRegistry;
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
            AdminAssetRegistry::class
        );

        $container->singleton(
            ViteAssetResolver::class,
            static fn(): ViteAssetResolver =>
            new ViteAssetResolver(
                dirname(__DIR__, 4)
                . '/public/dist/.vite/manifest.json',
                '/public/dist/'
            )
        );

        $container->singleton(
            SidebarRegistry::class
        );

        $container->singleton(
            AdminUIAssets::class
        );

        $container->singleton(
            AdminUIRenderer::class
        );

        $sidebarRegistry = $container->get(
            SidebarRegistry::class
        );

        if (
            !$sidebarRegistry->has(
                SidebarRegistry::DEFAULT_SIDEBAR
            )
        ) {
            $sidebarRegistry->create(
                SidebarRegistry::DEFAULT_SIDEBAR,
                'Основна навигация',
                'left'
            );
        }

        $container->singleton(
            ViteAssetResolver::class,
            static fn(): ViteAssetResolver =>
            new ViteAssetResolver(
                manifestPath:
                dirname(__DIR__, 4)
                . '/public/dist/.vite/manifest.json',

                baseUrl:
                '/public/dist/',

                development:
                ($_ENV['APP_ENV'] ?? 'production')
                === 'development',

                devServerUrl:
                'http://localhost:3000'
            )
        );
    }
}
