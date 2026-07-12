<?php

namespace Flex\Core\Routing;

class View
{
    public function __construct(
        public string $path,
        public array $data = [],
        public string $layout = 'main',
        public string $source = 'core'
    ) {
    }

    public static function make(string $path, array $data = [], string $layout = 'main', string $source = 'core'): self
    {
        return new self($path, $data, $layout, $source);
    }

    public static function redirect(string $url, int $code = 302, bool $replace = true): void
    {
        header("Location: " . $url, $replace, $code);
        exit;
    }

    public static function jsonResponse(array $data, int $statusCode = 200)
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }

    public static function component(string $componentName, array $data = [], string $folder = "components"): void
    {
        extract($data);

        $folderPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $folder);

        $basePath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'views';

        $filePath = $basePath . DIRECTORY_SEPARATOR . $folderPath . DIRECTORY_SEPARATOR . $componentName . '.php';

        if (file_exists($filePath)) {
            include $filePath;
        } else {
            error_log("Component not found: " . $filePath);
            if (($_ENV['DEBUG'] ?? true) === true) {
                echo "<!-- Component $componentName not found в $filePath -->";
            }
        }
    }

    public static function renderTheme(string $path, array $data = [], string $layout = 'main'): void
    {
        $helperClass = "Themes\\" . ACTIVE_THEME . "\\Helpers\\ThemeHelper";

        $globals = [];
        if (class_exists($helperClass) && method_exists($helperClass, 'getGlobals')) {
            $globals = $helperClass::getGlobals();
        }

        $data = array_merge($globals, $data);

        $view = self::make($path, $data, $layout, 'theme');

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

    public static function dispatchOptions(object $page, $location): void
    {
        $templateKey = preg_replace('/[^a-zA-Z0-9\-]/', '', $page->options['page_template'] ?? 'default');
        $templateName = ucfirst($templateKey);

        $isOptions = ($location === 'options');

        $folder = $isOptions ? 'PageOptions' : 'PageTemplates';
        $suffix = $isOptions ? $page->options['page_options_key'] : "Template";

        $class = "\\Themes\\" . ACTIVE_THEME . "\\" . $folder . "\\" . $templateName . $suffix;

        if (!class_exists($class)) {
            $defaultSuffix = $isOptions ? "DefaultOptions" : "DefaultTemplate";
            $class = "\\Themes\\" . ACTIVE_THEME . "\\" . $folder . "\\" . $defaultSuffix;
        }

        if (class_exists($class)) {
            $instance = new $class($page->id, $page);
            if (method_exists($instance, 'render')) {
                $instance->render();
            }
        }
    }
}
