<?php

namespace Flex\Core\UI;

class Sidebar
{
    protected static array $sidebars = [];

    public static function register(string $name, array $initialLinks = []): void
    {
        if (!isset(self::$sidebars[$name])) {
            self::$sidebars[$name] = $initialLinks;
        }
    }

    public static function addLink(string $sidebarName, array $link, ?int $index = null): void
    {
        if (!isset(self::$sidebars[$sidebarName])) {
            return;
        }

        if ($index === null || $index >= count(self::$sidebars[$sidebarName])) {
            self::$sidebars[$sidebarName][] = $link;
        } else {
            array_splice(self::$sidebars[$sidebarName], $index, 0, [$link]);
        }
    }

    public static function addChildLink(string $sidebarName, string $parentUrl, array $childLink, ?int $index = null): void
    {
        if (!isset(self::$sidebars[$sidebarName])) {
            return;
        }

        foreach (self::$sidebars[$sidebarName] as &$link) {
            if ($link['url'] === $parentUrl) {
                if (!isset($link['children'])) {
                    $link['children'] = [];
                }

                if ($index === null || $index >= count($link['children'])) {
                    $link['children'][] = $childLink;
                } else {
                    array_splice($link['children'], $index, 0, [$childLink]);
                }
                break;
            }
        }
    }

    public static function getLinks(string $sidebarName): array
    {
        return self::$sidebars[$sidebarName] ?? [];
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
    ['url' => '/admin/plugins', 'icon' => 'fa-plug', 'label' => 'Плъгини'],
    [
        'url' => '/admin/settings',
        'icon' => 'fa-cogs',
        'label' => 'Настройки',
        'children' => [
            ['url' => '/admin/settings/general', 'label' => 'Общи'],
            ['url' => '/admin/settings/mail', 'label' => 'Поща'],
            ['url' => '/admin/settings/system', 'label' => 'Системни'],
            ['url' => '/admin/settings/security', 'label' => 'Сигурност'],
        ]
    ],
    ['url' => '/admin/update', 'icon' => 'fa-arrow-rotate-right', 'label' => 'Обновяване'],
]);

Sidebar::register('shop_admin');