<?php

namespace Flex\Core\Controllers;

use Flex\Attributes\UseExceptions;
use Flex\Core\Services\Cache;
use Flex\Models\Setting;
use Flex\Models\Theme;
use Flex\Core\Routing\View;

class ThemeController extends BaseController
{
    #[UseExceptions]
    public function index(): void
    {
        $data = [
            'title' => 'Инсталирани теми',
            'themes' => Theme::all()
        ];

        render_view('admin/themes/index', $data, 'core', 'admin', 'core');
    }

    #[UseExceptions]
    public function activate()
    {
        $folder = $_POST['folder'] ?? null;

        if (!$folder) {
            $_SESSION['flash_error'] = 'Невалидна тема.';
            View::redirect('/admin/themes/all');
        }

        Setting::updateOrCreate(
            ['key' => 'active_theme'],
            [
                'value' => $folder,
                'group' => 'system',
                'type' => 'string'
            ]
        );

        Cache::clear('views');

        $_SESSION['flash_success'] = "Темата '{$folder}' беше активирана успешно.";

        View::redirect('/admin/themes/all');
    }

    #[UseExceptions]
    public function deactivate()
    {
        $folder = $_POST['folder'] ?? null;

        if (!$folder) {
            $_SESSION['flash_error'] = 'Невалидна тема.';
            View::redirect('/admin/themes/all');
        }

        $deleted = Setting::where('key', 'active_theme')
            ->where('value', $folder)
            ->delete();

        if ($deleted) {
            Cache::clear('views');
            $_SESSION['flash_success'] = "Темата '{$folder}' беше деактивирана.";
        } else {
            $_SESSION['flash_error'] = 'Тази тема не е активна или не съществува.';
        }

        View::redirect('/admin/themes/all');
    }
}
