<?php

namespace Flex\Core;

class Vite
{
    private static string $manifestPath = '/public/dist/.vite/manifest.json';
    private static string $devServer = 'http://localhost';

    private string $entry;
    private string $type;
    private int $port = 5173;

    public function __construct(string $entry, string $type = 'core')
    {
        $this->entry = $entry;
        $this->type = $type;
    }

    private function isDev(): bool
    {
        $handle = @fsockopen("localhost", $this->port, $errno, $errstr, 0.1);
        if ($handle) {
            fclose($handle);
            return true;
        }
        return false;
    }

    public static function use(string $entry, string $type = 'core'): self
    {
        return new self($entry, $type);
    }

    public function port(int $port): self
    {
        $this->port = $port;
        return $this;
    }

    public function __toString(): string
    {
        $entry = $this->entry;
        $type = $this->type;
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

        $currentDevServer = self::$devServer . ":" . $this->port;

        if ($this->isDev()) {
            $devUrl = $currentDevServer . "/" . ($isTheme ? "themes/" . ACTIVE_THEME . "/" : "") . $entryPath;
            return sprintf(
                '<script type="module" src="%1$s/@vite/client"></script>' . PHP_EOL .
                '<script type="module" src="%2$s" defer></script>',
                $currentDevServer,
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
