<?php

namespace Flex\Core\Controllers;

use Flex\Attributes\UseExceptions;
use Flex\Core\Controllers\BaseController;

class FileStructureController extends BaseController
{
    #[UseExceptions]
    public function index()
    {
        $rootPath = dirname(__DIR__, 3);

        $exclude = [
            '.git',
            'node_modules',
            'vendor',
            'storage',
            '.env',
            '.idea',
            'public',
            '.dist',
            'pnpm-lock.yaml',
            '.DS_Store'
        ];

        $fileTree = $this->buildTree($rootPath, $exclude);

        $data = [
            'title' => 'Структура на файловете',
            'fileTree' => $fileTree,
        ];

        render_view('admin/file-structure/index', $data);
    }

    #[UseExceptions]
    private function buildTree(string $dir, array $exclude): array
    {
        $dir = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if (!is_dir($dir))
            return [];

        $items = scandir($dir);
        if ($items === false)
            return [];

        $tree = [];
        $folders = [];
        $files = [];

        foreach ($items as $item) {
            if (in_array($item, ['.', '..']) || in_array($item, $exclude)) {
                continue;
            }

            if (is_dir($dir . $item)) {
                $folders[] = $item;
            } else {
                $files[] = $item;
            }
        }

        sort($folders);
        sort($files);

        foreach ($folders as $folder) {
            $tree[] = [
                'name' => $folder,
                'type' => 'folder',
                'children' => $this->buildTree($dir . $folder, $exclude)
            ];
        }

        foreach ($files as $file) {
            $tree[] = [
                'name' => $file,
                'type' => 'file',
                'extension' => pathinfo($file, PATHINFO_EXTENSION)
            ];
        }

        return $tree;
    }
}
