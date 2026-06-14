<?php

use Flex\Core\Routing\View;
use Flex\Models\Setting;

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        return __DIR__ . '/' . $path;
    }
}

if (!function_exists('themes_path')) {
    function themes_path(string $path = ''): string
    {
        return __DIR__ . '/themes/' . $path;
    }
}

if (!function_exists('plugins_path')) {
    function plugins_path(string $path = ''): string
    {
        return __DIR__ . '/plugins/' . ltrim($path, '/');
    }
}

if (!function_exists('render_view')) {
    function render_view(View $view, bool $returnOutput = false): ?string
    {
        extract($view->data);

        if ($view->source === 'theme') {
            $fullViewPath = __DIR__ . '/themes/' . ACTIVE_THEME . '/views/' . $view->path . '.php';
        } else {
            $fullViewPath = __DIR__ . '/app/views/' . $view->path . '.php';
        }
        
        $layoutPath = __DIR__ . "/app/views/layouts/{$view->layout}.php";

        ob_start();
        if (file_exists($fullViewPath)) {
            include $fullViewPath;
        } else {
            throw new \Exception("View not found: " . htmlspecialchars($fullViewPath));
        }
        $content = ob_get_clean();

        if (file_exists($layoutPath)) {
            ob_start();
            include $layoutPath;
            $finalOutput = ob_get_clean();
        } else {
            $finalOutput = $content;
        }

        if ($returnOutput) {
            return $finalOutput;
        }

        echo $finalOutput;
        exit;
    }
}

if (!function_exists('handle_exception')) {
    function handle_exception(Throwable $e): void
    {
        error_log($e->getMessage());

        $debugMode = Setting::get('debug_mode', false);
        
        $data = [
            'message'   => $debugMode ? $e->getMessage() : 'Съжаляваме, възникна неочаквана грешка.',
            'debugMode' => $debugMode,
            'file'      => $e->getFile(),
            'line'      => $e->getLine(),
            'trace'     => $e->getTraceAsString()
        ];

        $view = View::make('errors/500', $data, 'main', 'core');
        
        echo render_view($view, true);
        exit;
    }
}
