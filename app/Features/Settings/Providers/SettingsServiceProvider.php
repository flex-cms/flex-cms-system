<?php

declare(strict_types=1);

namespace Flex\Features\Settings\Providers;

use Flex\Core\Container\Container;
use Flex\Core\Features\Contracts\FeatureServiceProviderInterface;
use Flex\Features\Settings\Configuration\SettingsPageConfig;
use Flex\Features\Settings\Repositories\EloquentSettingRepository;
use Flex\Features\Settings\Repositories\SettingRepositoryInterface;
use Flex\Features\Settings\Services\SettingsService;

final class SettingsServiceProvider implements
    FeatureServiceProviderInterface
{
    public function register(Container $container): void
    {
        $container->singleton(
            SettingsPageConfig::class
        );

        $container->singleton(
            SettingRepositoryInterface::class,
            EloquentSettingRepository::class
        );

        $container->singleton(
            SettingsService::class
        );
    }
}