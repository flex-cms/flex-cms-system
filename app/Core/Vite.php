<?php

namespace Flex\Core;

class Vite
{
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

    public static function use(string $entry, string $type = 'core'): string
    {
        $isTheme = ($type === 'theme');

        if ($isTheme) {
            $manifestPath = "themes/" . ACTIVE_THEME . "/assets/dist/.vite/manifest.json";
            $publicPath = "/themes/" . ACTIVE_THEME . "/assets/dist/";
            $entryPath = "resources/js/{$entry}.js";
        } else {
            $manifestPath = ltrim(self::$manifestPath, '/');
            $publicPath = "/public/dist/";
            $entryPath = "resources/js/{$entry}.js";
        }

        if (self::isDev()) {
            $devUrl = self::$devServer . "/" . ($isTheme ? "themes/" . ACTIVE_THEME . "/" : "") . $entryPath;
            return sprintf(
                '<script type="module" src="%1$s/@vite/client"></script>' . PHP_EOL .
                '<script type="module" src="%2$s" defer></script>',
                self::$devServer,
                $devUrl
            );
        }

        $fullManifestPath = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/' . $manifestPath;

        if (!file_exists($fullManifestPath)) {
            return "";
        }

        $manifest = json_decode(file_get_contents($fullManifestPath), true);
        if (!isset($manifest[$entryPath])) {
            return "";
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
            $html .= sprintf('<link rel="stylesheet" href="%s%s">' . PHP_EOL, $publicPath, $cssFile);
        }

        $html .= sprintf('<script type="module" src="%s%s"></script>', $publicPath, $asset['file']);

        return $html;
    }
}