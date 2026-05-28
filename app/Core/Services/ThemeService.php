<?php

namespace Flex\Core\Services;

class ThemeService
{
    public static function render(string $view, array $data = []): void
    {
        extract($data);

        $file = __DIR__ . '/../../../themes/' . ACTIVE_THEME . '/Views/' . $view . '.php';

        if (file_exists($file)) {
            require $file;
        } else {
            die("View file not found: " . $file);
        }
    }

    public static function getThemeViewPath(string $viewPath): string
    {
        return dirname(__DIR__, 4) . '/themes/' . ACTIVE_THEME . '/views/' . $viewPath . '.php';
    }
}