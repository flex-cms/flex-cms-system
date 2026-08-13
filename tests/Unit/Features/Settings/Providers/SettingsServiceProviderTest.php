<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Settings\Providers;

use Flex\Core\Assets\AdminAssetRegistry;
use Flex\Core\Container\Container;
use Flex\Core\Features\Contracts\FeatureServiceProviderInterface;
use Flex\Features\AdminUI\Navigation\SidebarRegistry;
use Flex\Features\Settings\Navigation\SettingsNavigation;
use Flex\Features\Settings\Providers\SettingsServiceProvider;
use Flex\Features\Settings\Repositories\EloquentSettingRepository;
use Flex\Features\Settings\Repositories\SettingRepositoryInterface;
use Flex\Features\Settings\Services\SettingsService;
use PHPUnit\Framework\TestCase;

final class SettingsServiceProviderTest extends TestCase
{
    public function testItImplementsFeatureProviderContract(): void
    {
        self::assertInstanceOf(
            FeatureServiceProviderInterface::class,
            new SettingsServiceProvider()
        );
    }

    public function testItRegistersSettingsDependencies(): void
    {
        $container = $this->containerWithAdminDependencies();

        (new SettingsServiceProvider())->register($container);

        self::assertTrue($container->has(SettingRepositoryInterface::class));
        self::assertTrue($container->has(SettingsService::class));
        self::assertTrue($container->has(SettingsNavigation::class));
    }

    public function testItBindsRepositoryInterfaceToEloquentRepository(): void
    {
        $container = $this->containerWithAdminDependencies();

        (new SettingsServiceProvider())->register($container);

        self::assertInstanceOf(
            EloquentSettingRepository::class,
            $container->get(SettingRepositoryInterface::class)
        );
    }

    public function testRegisteredDependenciesAreSingletons(): void
    {
        $container = $this->containerWithAdminDependencies();

        (new SettingsServiceProvider())->register($container);

        foreach ([
            SettingRepositoryInterface::class,
            SettingsService::class,
            SettingsNavigation::class,
        ] as $service) {
            self::assertSame(
                $container->get($service),
                $container->get($service)
            );
        }
    }

    public function testItRegistersNavigationAndAssets(): void
    {
        $container = $this->containerWithAdminDependencies();

        (new SettingsServiceProvider())->register($container);

        $sidebars = $container->get(SidebarRegistry::class);
        self::assertNotNull(
            $sidebars->sidebar()->find('settings')
        );

        self::assertContains(
            'app/Features/Settings/Resources/js/settings.js',
            $container->get(AdminAssetRegistry::class)->scripts()
        );
    }

    private function containerWithAdminDependencies(): Container
    {
        $container = new Container();
        $sidebars = new SidebarRegistry();
        $sidebars->create(
            SidebarRegistry::DEFAULT_SIDEBAR,
            'Administration'
        );

        $container->instance(SidebarRegistry::class, $sidebars);
        $container->instance(
            AdminAssetRegistry::class,
            new AdminAssetRegistry()
        );

        return $container;
    }
}
