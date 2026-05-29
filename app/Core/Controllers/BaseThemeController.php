<?php

namespace Flex\Core\Controllers;

use Flex\Core\Auth;
use Flex\Core\Routing\View;
use Flex\Models\Setting;

trait ViewRendererTrait
{
    protected function render(View $view): void
    {
        extract($view->data);

        $root = dirname(__DIR__, 3);
        $fullViewPath = $root . '/themes/' . ACTIVE_THEME . '/views/' . $view->path . '.php';
        $layoutPath = $root . '/themes/' . ACTIVE_THEME . '/views/layouts/' . $view->layout . '.php';

        ob_start();
        if (file_exists($fullViewPath)) {
            include $fullViewPath;
        } else {
            die("View not found: " . htmlspecialchars($fullViewPath));
        }
        $content = ob_get_clean();

        if (file_exists($layoutPath)) {
            include $layoutPath;
        } else {
            echo $content;
        }
        exit;
    }
}

class BaseThemeController
{
    use ViewRendererTrait;

    protected function getThemeGlobals(): array
    {
        $user = Auth::user();
        $settings = $this->getThemeSettings();

        $enableDark = (bool) ($settings['enable_dark_mode'] ?? false);
        $theme = $user->options['theme'] ?? ($_SESSION['dark_mode'] ?? false ? 'dark' : 'light');
        
        return [
            'settings'          => $settings,
            'enableDark'        => $enableDark,
            'darkMode'          => $enableDark ? ($theme === 'dark') : false,
            'allowRegistration' => (bool) ($settings['allow_registration'] ?? false),
        ];
    }

    protected function renderTheme(string $viewPath, array $data = [], string $layout = 'main')
    {
        $globals = $this->getThemeGlobals();
        $data = array_merge($globals, $data);
        
        $themeSettings = [];
        $settingsClass = "Themes\\" . ACTIVE_THEME . "\\Models\\ThemeSettings";

        if (class_exists($settingsClass) && method_exists($settingsClass, 'get')) {
            $themeSettings = $settingsClass::get();
        }

        $customSettings = $data['settings'] ?? [];
        $data['settings'] = array_merge((array) $themeSettings, (array) $customSettings);

        return $this->render(View::make($viewPath, $data, $layout, 'theme'));
    }

    protected function getThemeSettings(string $key = 'theme_basic_settings')
    {
        $settings = Setting::get($key, []);
        return is_array($settings) ? $settings : [];
    }

    protected function jsonResponse(array $data, int $statusCode = 200)
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }
}