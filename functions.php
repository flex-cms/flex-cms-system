<?php

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

if (!function_exists('current_theme')) {
    function current_theme(string $path = ''): string
    {
        return __DIR__ . '/themes/' . ACTIVE_THEME . '/' . $path;
    }
}

if (!function_exists('plugins_path')) {
    function plugins_path(string $path = ''): string
    {
        return __DIR__ . '/plugins/' . ltrim($path, '/');
    }
}

if (!function_exists('render_view')) {
    function render_view(
        string $path,
        array $data = [],
        string $viewSource = 'core',
        string $layout = 'admin',
        string $layoutSource = 'core'
    ): void {
        extract($data);
        $root = __DIR__;

        $viewDir = ($viewSource === 'theme') ? "/themes/" . ACTIVE_THEME . "/views/" : "/app/views/";
        $layoutDir = ($layoutSource === 'theme') ? "/themes/" . ACTIVE_THEME . "/views/layouts/" : "/app/views/layouts/";

        $fullViewPath = $root . $viewDir . $path . '.php';
        $layoutPath = $root . $layoutDir . $layout . '.php';

        ob_start();
        if (file_exists($fullViewPath)) {
            include $fullViewPath;
        } else {
            throw new \Exception("View not found: " . $fullViewPath);
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

if (!function_exists('input')) {
    function input(string $key, $default = null)
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }
}

if (!function_exists('is_api_request')) {
    function is_api_request(): bool
    {
        $acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? '';
        $uri = $_SERVER['REQUEST_URI'] ?? '';

        return str_contains($acceptHeader, 'application/json') || str_starts_with($uri, '/api/');
    }
}

if (!function_exists('handle_exception')) {
    function handle_exception(Throwable $e): void
    {
        error_log($e->getMessage());

        $debugMode = Setting::get('debug_mode', false);

        if (is_api_request()) {
            header('Content-Type: application/json');
            http_response_code(500);

            $response = [
                'status' => 'error',
                'message' => $debugMode ? $e->getMessage() : 'Възникна вътрешна грешка в сървъра.'
            ];

            if ($debugMode) {
                $response['debug'] = [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTrace()
                ];
            }

            echo json_encode($response);
            exit;
        }

        $data = [
            'message' => $debugMode ? $e->getMessage() : 'Съжаляваме, възникна неочаквана грешка.',
            'debugMode' => $debugMode,
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ];

        render_view('errors/500', $data, 'core', 'main');
        exit;
    }
}

if (!function_exists('get_json_data')) {
    function get_json_data(string $filePath, ?string $key = null, $default = null)
    {
        static $cache = [];

        if (!isset($cache[$filePath])) {
            if (file_exists($filePath)) {
                $cache[$filePath] = json_decode(file_get_contents($filePath), true) ?? [];
            } else {
                $cache[$filePath] = [];
            }
        }

        $data = $cache[$filePath];

        if ($key === null) {
            return $data;
        }

        $keys = explode('.', $key);
        foreach ($keys as $segment) {
            if (!isset($data[$segment])) {
                return $default;
            }
            $data = $data[$segment];
        }

        return $data;
    }
}

if (!function_exists('theme_info')) {
    function theme_info(?string $key = null, $default = null)
    {
        $path = themes_path(ACTIVE_THEME . '/theme.json');
        return get_json_data($path, $key, $default);
    }
}

if (!function_exists('core_info')) {
    function core_info(?string $key = null, $default = null)
    {
        $path = base_path('core.json');
        return get_json_data($path, $key, $default);
    }
}

if (!function_exists('ddj')) {
    function ddj($data)
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}

if (!function_exists('base_url')) {
    function base_url(string $path = ''): string
    {
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        
        $baseUrl = $protocol . "://" . $host;
        
        return $baseUrl . '/' . ltrim($path, '/');
    }
}
