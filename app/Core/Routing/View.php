<?php

namespace Flex\Core\Routing;

use Flex\Models\Page;

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

    public static function component(string $componentName, array $data = [], string $folder = "components", string $source = 'core'): void
    {
        extract($data);

        $folderPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $folder);

        $rootPath = base_path();

        if ($source === 'theme' && defined('ACTIVE_THEME') && ACTIVE_THEME) {
            $basePath = $rootPath . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . ACTIVE_THEME . DIRECTORY_SEPARATOR . 'views';
        } else {
            $basePath = $rootPath . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'views';
        }

        $filePath = $basePath . DIRECTORY_SEPARATOR . $folderPath . DIRECTORY_SEPARATOR . $componentName . '.php';

        if (file_exists($filePath)) {
            include $filePath;
        } else {
            error_log("Component not found: " . $filePath);
            if (($_ENV['DEBUG'] ?? true) === true) {
                echo "";
            }
        }
    }

    public static function dispatchOptions(
        $page,
        string $folder = 'elements'
    ): void {
        $key = $page->getOption('page_options_key');

        if (!$key) {
            return;
        }

        $key = strtolower(trim($key));

        self::component(
            $key,
            [
                'page' => $page,
                'options' => $page->getOptions(),
                'elements' => $page->elements,
            ],
            'admin/' . $folder,
            'theme'
        );
    }
}
