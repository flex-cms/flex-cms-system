<?php

namespace Flex\Core\Plugins;

use Flex\Core\Events\EventManager;
use Flex\Core\Plugins\Contracts\PluginServiceProviderInterface;
use Flex\Core\Routing\Router;
use Flex\Core\UI\Sidebar;
use RuntimeException;

abstract class PluginServiceProvider implements PluginServiceProviderInterface
{
    protected bool $routesLoaded = false;

    protected bool $adminMenuLoaded = false;

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

    protected function loadRoutes(): void
    {
        if ($this->routesLoaded) {
            return;
        }

        foreach (['web', 'admin', 'api'] as $type) {
            $this->loadRouteFile($type);
        }

        $this->routesLoaded = true;
    }

    protected function loadRouteFile(string $type): void
    {
        $routes = $this->manifest['routes'] ?? [];
        $relativePath = $routes[$type] ?? null;

        if (!is_string($relativePath) || trim($relativePath) === '') {
            return;
        }

        $routeFile = $this->getPluginPath($relativePath);

        if (!is_file($routeFile)) {
            throw new RuntimeException(
                "Route файлът „{$relativePath}“ не съществува."
            );
        }

        $router = $this->router;
        $manifest = $this->manifest;
        $pluginPath = $this->pluginPath;
        $routeType = $type;

        require $routeFile;
    }

    protected function loadAdminMenu(): void
    {
        if ($this->adminMenuLoaded) {
            return;
        }

        $menu = $this->manifest['admin_menu'] ?? null;

        if ($menu === null || $menu === false) {
            $this->adminMenuLoaded = true;

            return;
        }

        if (!is_array($menu)) {
            throw new RuntimeException(
                'Полето „admin_menu“ трябва да бъде обект или false.'
            );
        }

        Sidebar::addManifestMenu(
            'admin_main',
            $menu,
            $this->getPluginSlug()
        );

        $this->adminMenuLoaded = true;
    }

    protected function getPluginSlug(): string
    {
        $slug = trim((string) ($this->manifest['slug'] ?? ''));

        if ($slug === '') {
            throw new RuntimeException(
                'Плъгинът няма валиден slug за регистрация на admin меню.'
            );
        }

        return $slug;
    }

    public function getManifest(): array
    {
        return $this->manifest;
    }

    public function getPluginPath(?string $path = null): string
    {
        if ($path === null || trim($path) === '') {
            return $this->pluginPath;
        }

        return $this->pluginPath
            . DIRECTORY_SEPARATOR
            . ltrim($path, '/\\');
    }
}
