<?php

declare(strict_types=1);

namespace Tests\Unit\Features\AdminUI\Providers;

use Flex\Core\Container\Container;
use Flex\Core\Features\Contracts\FeatureServiceProviderInterface;
use Flex\Core\View\Contracts\ViewRendererInterface;
use Flex\Features\AdminUI\Configuration\AdminUIConfig;
use Flex\Features\AdminUI\Providers\AdminUIServiceProvider;
use Flex\Features\AdminUI\Services\AdminUIAssets;
use Flex\Features\AdminUI\Services\AdminUIRenderer;
use PHPUnit\Framework\TestCase;

final class AdminUIServiceProviderTest extends TestCase
{
    public function testItImplementsFeatureProviderContract(): void
    {
        $provider =
            new AdminUIServiceProvider();

        self::assertInstanceOf(
            FeatureServiceProviderInterface::class,
            $provider
        );
    }

    public function testItRegistersAdminUIDependencies(): void
    {
        $container = new Container();

        (new AdminUIServiceProvider())
            ->register($container);

        self::assertTrue(
            $container->has(
                AdminUIConfig::class
            )
        );

        self::assertTrue(
            $container->has(
                AdminUIAssets::class
            )
        );

        self::assertTrue(
            $container->has(
                AdminUIRenderer::class
            )
        );
    }

    public function testItBuildsAdminUIAssets(): void
    {
        $container = new Container();

        (new AdminUIServiceProvider())
            ->register($container);

        $assets = $container->get(
            AdminUIAssets::class
        );

        self::assertInstanceOf(
            AdminUIAssets::class,
            $assets
        );
    }

    public function testRegisteredServicesAreSingletons(): void
    {
        $container = new Container();

        $container->instance(
            ViewRendererInterface::class,
            $this->createMock(
                ViewRendererInterface::class
            )
        );

        (new AdminUIServiceProvider())
            ->register($container);

        self::assertSame(
            $container->get(
                AdminUIConfig::class
            ),
            $container->get(
                AdminUIConfig::class
            )
        );

        self::assertSame(
            $container->get(
                AdminUIAssets::class
            ),
            $container->get(
                AdminUIAssets::class
            )
        );

        self::assertSame(
            $container->get(
                AdminUIRenderer::class
            ),
            $container->get(
                AdminUIRenderer::class
            )
        );
    }

    public function testItBuildsAdminUIRenderer(): void
    {
        $container = new Container();

        $views = $this->createMock(
            ViewRendererInterface::class
        );

        $container->instance(
            ViewRendererInterface::class,
            $views
        );

        (new AdminUIServiceProvider())
            ->register($container);

        $renderer = $container->get(
            AdminUIRenderer::class
        );

        self::assertInstanceOf(
            AdminUIRenderer::class,
            $renderer
        );
    }
}
