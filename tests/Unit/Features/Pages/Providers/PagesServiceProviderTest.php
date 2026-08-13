<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Pages\Providers;

use Flex\Core\Container\Container;
use Flex\Core\Features\Contracts\FeatureServiceProviderInterface;
use Flex\Features\AdminUI\Navigation\DefaultAdminNavigation;
use Flex\Features\AdminUI\Navigation\SidebarRegistry;
use Flex\Features\Pages\Navigation\PagesNavigation;
use Flex\Features\Pages\Providers\PagesServiceProvider;
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
use PHPUnit\Framework\TestCase;

final class PagesServiceProviderTest extends TestCase
{
    public function testItImplementsFeatureProviderContract(): void
    {
        self::assertInstanceOf(
            FeatureServiceProviderInterface::class,
            new PagesServiceProvider()
        );
    }

    public function testItBindsAllRepositoryContracts(): void
    {
        $container = $this->registeredContainer();

        self::assertInstanceOf(
            EloquentPageRepository::class,
            $container->get(PageRepositoryInterface::class)
        );
        self::assertInstanceOf(
            EloquentPageOptionRepository::class,
            $container->get(PageOptionRepositoryInterface::class)
        );
        self::assertInstanceOf(
            EloquentPageElementRepository::class,
            $container->get(PageElementRepositoryInterface::class)
        );
    }

    public function testItRegistersServicesAsSingletons(): void
    {
        $container = $this->registeredContainer();

        foreach ([
            PageRepositoryInterface::class,
            PageOptionRepositoryInterface::class,
            PageElementRepositoryInterface::class,
            PageTreeService::class,
            PageService::class,
            PageOptionService::class,
            PageElementService::class,
            PagesNavigation::class,
        ] as $service) {
            self::assertSame(
                $container->get($service),
                $container->get($service)
            );
        }
    }

    public function testItRegistersPagesNavigation(): void
    {
        $container = $this->registeredContainer();
        $registry = $container->get(SidebarRegistry::class);

        self::assertTrue($registry->sidebar()->has('pages'));
        self::assertSame(
            '/admin/pages',
            $registry->sidebar()->find('pages')?->toArray()['url']
        );
    }

    public function testFeatureManifestDeclaresProviderAndAdminRoutes(): void
    {
        $manifest = require dirname(__DIR__, 5)
            . '/app/Features/Pages/feature.php';

        self::assertTrue($manifest['enabled']);
        self::assertSame(8, $manifest['priority']);
        self::assertSame(
            [PagesServiceProvider::class],
            $manifest['providers']
        );
        self::assertSame(
            [
                'admin' => 'Routes/admin.php',
                'api' => 'Routes/api.php',
            ],
            $manifest['routes']
        );
    }

    private function registeredContainer(): Container
    {
        $container = new Container();
        $registry = new SidebarRegistry();
        (new DefaultAdminNavigation($registry))->register();
        $container->instance(SidebarRegistry::class, $registry);

        (new PagesServiceProvider())->register($container);

        return $container;
    }
}
