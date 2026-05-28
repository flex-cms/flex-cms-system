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

    public static function redirect(string $url, int $code = 302): void
    {
        header("Location: " . $url, true, $code);
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
}
