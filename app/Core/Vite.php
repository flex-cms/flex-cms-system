<?php

namespace Flex\Core;

class Vite
{
    private static string $outDir = 'dist';
    private static string $manifestPath = '/public/dist/.vite/manifest.json';
    private static string $devServer = 'http://localhost:5173';

    private static function isDev(): bool
    {
        $handle = @fsockopen("localhost", 5173, $errno, $errstr, 0.1);
        if ($handle) {
            fclose($handle);
            return true;
        }
        return false;
    }

    public static function use(string $entry): string
    {
        $entryPath = "resources/js/{$entry}.js";

        if (self::isDev()) {
            return sprintf(
                '<script type="module" src="%1$s/@vite/client"></script>' . PHP_EOL .
                '<link rel="stylesheet" href="%1$s/resources/css/app.css">' . PHP_EOL .
                '<script type="module" src="%1$s/resources/js/admin.js" defer></script>',
                self::$devServer,
            );
        }

        $root = rtrim($_SERVER['DOCUMENT_ROOT'], '/');
        $fullManifestPath = $root . self::$manifestPath;

        if (!file_exists($fullManifestPath)) {
            return "<!-- Vite Error: Manifest not found -->";
        }

        $manifest = json_decode(file_get_contents($fullManifestPath), true);
        if (!isset($manifest[$entryPath])) {
            return "<!-- Vite Error: Entry $entryPath not found -->";
        }

        $asset = $manifest[$entryPath];
        $html = "";

        $collectCss = function ($item, $manifest) use (&$collectCss) {
            $css = $item['css'] ?? [];
            if (!empty($item['imports'])) {
                foreach ($item['imports'] as $importKey) {
                    if (isset($manifest[$importKey])) {
                        $css = array_merge($css, $collectCss($manifest[$importKey], $manifest));
                    }
                }
            }
            return $css;
        };

        $allCss = array_unique($collectCss($asset, $manifest));

        foreach ($allCss as $cssFile) {
            $html .= sprintf('<link rel="stylesheet" href="/public/dist/%s">' . PHP_EOL, $cssFile);
        }

        $html .= sprintf('<script type="module" src="/public/dist/%s"></script>', $asset['file']);

        return $html;
    }
}
