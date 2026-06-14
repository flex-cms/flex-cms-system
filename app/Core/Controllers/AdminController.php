<?php

namespace Flex\Core\Controllers;

use Flex\Attributes\UseExceptions;
use Flex\Core\Auth;
use Flex\Core\Controllers\BaseController;
use Flex\Core\Routing\View;
use Flex\Models\User;

class AdminController extends BaseController
{
    #[UseExceptions]
    public function index()
    {
        $this->render(View::make('admin/dashboard', [], 'admin'));
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