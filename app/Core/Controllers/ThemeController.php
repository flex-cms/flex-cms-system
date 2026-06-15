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
        View::render('admin/themes/index', [
            'title' => 'Инсталирани теми',
            'themes' => Theme::all()
        ], 'core', 'admin');
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
                'type'  => 'string'
            ]
        );

        Cache::clear('views');

        $_SESSION['flash_success'] = "Темата '{$folder}' беше активирана успешно.";
        
        View::redirect('/admin/themes/all');
    }
}