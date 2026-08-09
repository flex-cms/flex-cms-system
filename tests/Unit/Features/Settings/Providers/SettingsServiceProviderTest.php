<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Settings\Providers;

use Flex\Core\Container\Container;
use Flex\Core\Features\Contracts\FeatureServiceProviderInterface;
use Flex\Features\Settings\Configuration\SettingsPageConfig;
use Flex\Features\Settings\Providers\SettingsServiceProvider;
use Flex\Features\Settings\Repositories\EloquentSettingRepository;
use Flex\Features\Settings\Repositories\SettingRepositoryInterface;
use Flex\Features\Settings\Services\SettingsService;
use PHPUnit\Framework\TestCase;

final class SettingsServiceProviderTest extends TestCase
{
    public function testItImplementsFeatureProviderContract(): void
    {
        $provider = new SettingsServiceProvider();

        self::assertInstanceOf(
            FeatureServiceProviderInterface::class,
            $provider
        );
    }

    public function testItRegistersSettingsDependencies(): void
    {
        $container = new Container();
        $provider = new SettingsServiceProvider();

        $provider->register($container);

        self::assertTrue(
            $container->has(SettingsPageConfig::class)
        );

        self::assertTrue(
            $container->has(SettingRepositoryInterface::class)
        );

        self::assertTrue(
            $container->has(SettingsService::class)
        );
    }

    public function testItBindsRepositoryInterfaceToEloquentRepository(): void
    {
        $container = new Container();

        (new SettingsServiceProvider())
            ->register($container);

        $repository = $container->get(
            SettingRepositoryInterface::class
        );

        self::assertInstanceOf(
            EloquentSettingRepository::class,
            $repository
        );
    }

    public function testRegisteredDependenciesAreSingletons(): void
    {
        $container = new Container();

        (new SettingsServiceProvider())
            ->register($container);

        self::assertSame(
            $container->get(SettingsPageConfig::class),
            $container->get(SettingsPageConfig::class)
        );

        self::assertSame(
            $container->get(
                SettingRepositoryInterface::class
            ),
            $container->get(
                SettingRepositoryInterface::class
            )
        );

        self::assertSame(
            $container->get(SettingsService::class),
            $container->get(SettingsService::class)
        );
    }
}
