<?php

namespace Flex\Core\UI;

class Sidebar
{
    protected static array $sidebars = [];

    public static function register(
        string $name,
        array $initialLinks = []
    ): void {
        if (isset(self::$sidebars[$name])) {
            return;
        }

        self::$sidebars[$name] = array_map(
            static function (array $link): array {
                $link['source'] = $link['source'] ?? 'core';
                $link['plugin'] = $link['plugin'] ?? null;

                return $link;
            },
            $initialLinks
        );
    }

    public static function addLink(
        string $sidebarName,
        array $link,
        ?int $position = null
    ): void {
        if (!isset(self::$sidebars[$sidebarName])) {
            return;
        }

        if ($position !== null) {
            $link['position'] = $position;
        }

        $link['position'] = (int) ($link['position'] ?? 100);

        self::$sidebars[$sidebarName][] = $link;

        self::sortLinks(self::$sidebars[$sidebarName]);
    }

    public static function addChildLink(
        string $sidebarName,
        string $parentUrl,
        array $childLink,
        ?int $position = null
    ): void {
        if (!isset(self::$sidebars[$sidebarName])) {
            return;
        }

        if ($position !== null) {
            $childLink['position'] = $position;
        }

        $childLink['position'] = (int) (
            $childLink['position'] ?? 100
        );

        foreach (self::$sidebars[$sidebarName] as &$link) {
            if (($link['url'] ?? null) !== $parentUrl) {
                continue;
            }

            if (
                !isset($link['children'])
                || !is_array($link['children'])
            ) {
                $link['children'] = [];
            }

            $link['children'][] = $childLink;

            self::sortLinks($link['children']);

            break;
        }

        unset($link);
    }

    public static function addManifestMenu(
        string $sidebarName,
        array $menu,
        ?string $pluginSlug = null
    ): void {
        if (!isset(self::$sidebars[$sidebarName])) {
            return;
        }

        $key = $pluginSlug
            ? "plugin.{$pluginSlug}"
            : null;

        if ($key !== null) {
            foreach (self::$sidebars[$sidebarName] as $existingLink) {
                if (($existingLink['key'] ?? null) === $key) {
                    return;
                }
            }
        }

        $link = self::normalizeManifestMenu(
            $menu,
            $pluginSlug
        );

        self::addLink(
            $sidebarName,
            $link,
            $link['position']
        );
    }

    protected static function normalizeManifestMenu(
        array $menu,
        ?string $pluginSlug = null
    ): array {
        $children = [];

        foreach ($menu['children'] ?? [] as $child) {
            if (!is_array($child)) {
                continue;
            }

            $children[] = self::normalizeManifestMenu(
                $child,
                $pluginSlug
            );
        }

        $link = [
            'key' => $pluginSlug
                ? "plugin.{$pluginSlug}"
                : null,

            'url' => (string) ($menu['url'] ?? '#'),
            'icon' => $menu['icon'] ?? null,

            'label' => trim(
                (string) (
                    $menu['title']
                    ?? $menu['label']
                    ?? ''
                )
            ),

            'position' => (int) ($menu['position'] ?? 100),
            'permission' => $menu['permission'] ?? null,

            'source' => 'plugin',
            'plugin' => $pluginSlug,
        ];

        if ($children !== []) {
            self::sortLinks($children);

            $link['children'] = $children;
        }

        return $link;
    }

    public static function getLinks(string $sidebarName): array
    {
        return self::$sidebars[$sidebarName] ?? [];
    }

    public static function getCoreLinks(string $sidebarName): array
    {
        return array_values(
            array_filter(
                self::getLinks($sidebarName),
                static fn(array $link): bool =>
                ($link['source'] ?? 'core') === 'core'
            )
        );
    }

    public static function getPluginLinks(string $sidebarName): array
    {
        return array_values(
            array_filter(
                self::getLinks($sidebarName),
                static fn(array $link): bool =>
                ($link['source'] ?? 'core') === 'plugin'
            )
        );
    }

    protected static function sortLinks(array &$links): void
    {
        usort(
            $links,
            static fn(array $first, array $second): int =>
            ($first['position'] ?? 100)
            <=>
            ($second['position'] ?? 100)
        );

        foreach ($links as &$link) {
            if (
                isset($link['children'])
                && is_array($link['children'])
            ) {
                self::sortLinks($link['children']);
            }
        }

        unset($link);
    }
}

Sidebar::register('admin_main', [
    ['url' => '/admin/dashboard', 'icon' => 'fa-chart-line', 'label' => 'Табло'],
    [
        'url' => '/admin/users',
        'icon' => 'fa-users',
        'label' => 'Потребители',
        'children' => [
            ['url' => '/admin/users/index', 'label' => 'Всички потребители'],
            ['url' => '/admin/users/roles', 'label' => 'Роли и права'],
            ['url' => '/admin/users/permissions', 'label' => 'Разрешения'],
        ]
    ],
    [
        'url' => '/admin/themes',
        'icon' => 'fa-envelope',
        'label' => 'Теми',
        'children' => [
            ['url' => '/admin/themes/all', 'label' => 'Всички']
        ]
    ],
    [
        'url' => '/admin/pages',
        'icon' => 'fa-file-lines',
        'label' => 'Страници'
    ],
    [
        'url' => '/admin/email-templates',
        'icon' => 'fa-envelope',
        'label' => 'Имейл шаблони'
    ],
    ['url' => '/admin/plugins', 'icon' => 'fa-plug', 'label' => 'Плъгини'],
    [
        'url' => '/admin/settings',
        'icon' => 'fa-cogs',
        'label' => 'Настройки',
    ],
    ['url' => '/admin/update', 'icon' => 'fa-arrow-rotate-right', 'label' => 'Обновяване'],
]);

$groups = core_info('settings_options.settings_page_groups');

foreach ($groups as $key => $group) {
    $childLink = [
        'label' => $group['label'],
        'url' => "/admin/settings/{$key}",
        'icon' => $group['icon'] ?? null
    ];

    Sidebar::addChildLink(
        'admin_main',
        '/admin/settings',
        $childLink
    );
}
