<?php

declare(strict_types=1);

namespace Flex\Features\Dashboard\Repositories;

use Flex\Core\Flex;
use Flex\Models\Menu;
use Flex\Models\Page;
use Flex\Models\Plugin;
use Flex\Models\Setting;
use Flex\Models\User;
use Illuminate\Support\Collection;

final class EloquentDashboardRepository implements DashboardRepositoryInterface
{
    public function statistics(): array
    {
        return [
            'users_count' => User::count(),
            'active_users_count' => User::where('is_active', true)->count(),
            'pages_count' => Page::count(),
            'active_pages_count' => Page::where('is_active', true)->count(),
            'menus_count' => Menu::count(),
            'active_menus_count' => Menu::where('is_active', true)->count(),
            'plugins_count' => Plugin::count(),
            'active_plugins_count' => Plugin::where('is_active', true)->count(),
        ];
    }

    public function recentPages(int $limit = 5): Collection
    {
        return Page::query()
            ->select(['id', 'name', 'full_slug', 'is_active', 'updated_at'])
            ->latest('updated_at')
            ->limit(max(1, $limit))
            ->get();
    }

    public function recentLogins(int $limit = 5): Collection
    {
        return User::query()
            ->select(['id', 'fullname', 'email', 'last_login'])
            ->whereNotNull('last_login')
            ->latest('last_login')
            ->limit(max(1, $limit))
            ->get();
    }

    public function systemInformation(): array
    {
        $versionFile = base_path('version.php');
        $versionData = is_file($versionFile) ? require $versionFile : [];
        $versionData = is_array($versionData) ? $versionData : [];

        return [
            'version' => $versionData['version'] ?? Flex::VERSION,
            'release_date' => $versionData['release_date'] ?? null,
            'php_version' => PHP_VERSION,
            'active_theme' => Setting::getValue('active_theme', 'Няма активна тема'),
        ];
    }
}
