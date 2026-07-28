<?php

namespace Flex\Core\Plugins;

use Flex\Core\Events\EventManager;
use Flex\Core\Plugins\Contracts\PluginServiceProviderInterface;
use Flex\Core\Routing\Router;

abstract class PluginServiceProvider implements PluginServiceProviderInterface
{
    public function __construct(
        protected EventManager $events,
        protected Router $router,
        protected array $manifest,
        protected string $pluginPath
    ) {
    }

    public function register(): void
    {
    }

    public function boot(): void
    {
    }

    public function getManifest(): array
    {
        return $this->manifest;
    }

    public function getPluginPath(?string $path = null): string
    {
        if ($path === null || $path === '') {
            return $this->pluginPath;
        }

        return $this->pluginPath . DIRECTORY_SEPARATOR .
            ltrim($path, '/\\');
    }
}
