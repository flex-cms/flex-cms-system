<?php

namespace Flex\Core\Controllers;

use Flex\Attributes\UseExceptions;
use Flex\Core\Auth;
use Flex\Core\Controllers\BaseController;
use Flex\Models\Menu;
use Flex\Models\Page;
use Flex\Models\Plugin;
use Flex\Models\Setting;
use Flex\Models\User;

class AdminController extends BaseController
{
    #[UseExceptions]
    public function index(): void
    {
        $stats = [
            'users_count' => User::count(),
            'active_users_count' => User::where('is_active', true)->count(),
            'pages_count' => Page::count(),
            'active_pages_count' => Page::where('is_active', true)->count(),
            'menus_count' => Menu::count(),
            'active_menus_count' => Menu::where('is_active', true)->count(),
            'plugins_count' => Plugin::count(),
            'active_plugins_count' => Plugin::where('is_active', true)->count(),
        ];

        $recentPages = Page::query()
            ->select(['id', 'name', 'full_slug', 'is_active', 'updated_at'])
            ->latest('updated_at')
            ->limit(5)
            ->get();

        $recentLogins = User::query()
            ->select(['id', 'fullname', 'email', 'last_login'])
            ->whereNotNull('last_login')
            ->latest('last_login')
            ->limit(5)
            ->get();

        $versionData = require base_path('version.php');

        $system = [
            'version' => $versionData['version'] ?? 'Неизвестна',
            'release_date' => $versionData['release_date'] ?? null,
            'php_version' => PHP_VERSION,
            'active_theme' => Setting::getValue('active_theme', 'Няма активна тема'),
        ];

        render_view('admin/dashboard', [
            'title' => 'Табло за управление',
            'stats' => $stats,
            'recentPages' => $recentPages,
            'recentLogins' => $recentLogins,
            'system' => $system,
        ], 'core', 'admin');
    }

    #[UseExceptions]
    public function toggleTheme()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $isDark = (bool) ($data['darkMode'] ?? false);

        $_SESSION['dark_mode'] = $isDark;
        $this->updateUserOptions('theme', $isDark ? 'dark' : 'light');

        echo json_encode(['status' => 'success']);
    }

    #[UseExceptions]
    public function toggleSidebar()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $isOpen = (bool) ($data['sidebarOpen'] ?? true);

        $_SESSION['sidebar_open'] = $isOpen;
        $this->updateUserOptions('sidebar_open', $isOpen);

        echo json_encode(['status' => 'success']);
    }

    #[UseExceptions]
    public function saveUiState()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        $sectionId = $data['section_id'] ?? null;
        $state = $data['state'] ?? true;

        if ($sectionId) {
            $user = Auth::user();

            $options = $user->options;
            $options['ui_states'][$sectionId] = (bool) $state;

            $user->options = $options;
            $user->save();

            $_SESSION['ui_states'][$sectionId] = (bool) $state;
        }

        return json_encode(['status' => 'success']);
    }

    #[UseExceptions]
    private function updateUserOptions(string $key, mixed $value): void
    {
        if (isset($_SESSION['user_id'])) {
            $user = User::find($_SESSION['user_id']);

            if ($user) {
                if ($user->options === null) {
                    $user->options = [];
                }

                $user->options[$key] = $value;
                $user->save();
            }
        }
    }
}