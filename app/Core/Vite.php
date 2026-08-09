<?php

declare(strict_types=1);

namespace Flex\Core;

use InvalidArgumentException;

final class Vite
{
    private static string $manifestPath =
        '/public/dist/.vite/manifest.json';

    private static string $devServer =
        'http://localhost';

    private int $port = 5173;

    private function __construct(
        private readonly string $entry,
        private readonly string $type = 'core',
        private readonly bool $explicitPath = false
    ) {
    }

    /**
     * Старият API остава напълно съвместим.
     *
     * Vite::use('admin')
     * → resources/js/admin.js
     */
    public static function use(
        string $entry,
        string $type = 'core'
    ): self {
        return new self(
            entry: $entry,
            type: $type
        );
    }

    /**
     * Нов API за произволна Vite entry точка.
     *
     * Vite::entry('resources/ui/admin/index.js')
     */
    public static function entry(
        string $entryPath
    ): self {
        $entryPath = self::normalizeEntryPath(
            $entryPath
        );

        return new self(
            entry: $entryPath,
            type: 'core',
            explicitPath: true
        );
    }

    public function port(int $port): self
    {
        if ($port < 1 || $port > 65535) {
            throw new InvalidArgumentException(
                'Vite port must be between 1 and 65535.'
            );
        }

        $this->port = $port;

        return $this;
    }

    public function __toString(): string
    {
        try {
            return $this->render();
        } catch (\Throwable $exception) {
            /*
             * __toString() не трябва да хвърля exception
             * при обикновено HTML визуализиране.
             */
            if (defined('APP_DEBUG') && APP_DEBUG) {
                return sprintf(
                    '<!-- Vite error: %s -->',
                    htmlspecialchars(
                        $exception->getMessage(),
                        ENT_QUOTES,
                        'UTF-8'
                    )
                );
            }

            return '';
        }
    }

    private function render(): string
    {
        $entryPath = $this->entryPath();
        $publicPath = $this->publicPath();

        if ($this->isDevServerAvailable()) {
            return $this->renderDevelopment(
                $entryPath
            );
        }

        return $this->renderProduction(
            $entryPath,
            $publicPath
        );
    }

    private function entryPath(): string
    {
        if ($this->explicitPath) {
            return $this->entry;
        }

        if ($this->type === 'theme') {
            return sprintf(
                'resources/js/%s.js',
                $this->entry
            );
        }

        return sprintf(
            'resources/js/%s.js',
            $this->entry
        );
    }

    private function publicPath(): string
    {
        if ($this->type === 'theme') {
            return sprintf(
                '/themes/%s/assets/dist/',
                ACTIVE_THEME
            );
        }

        return '/public/dist/';
    }

    private function manifestPath(): string
    {
        if ($this->type === 'theme') {
            return sprintf(
                'themes/%s/assets/dist/.vite/manifest.json',
                ACTIVE_THEME
            );
        }

        return ltrim(
            self::$manifestPath,
            '/'
        );
    }

    private function developmentUrl(
        string $entryPath
    ): string {
        $prefix = $this->type === 'theme'
            ? sprintf(
                'themes/%s/',
                ACTIVE_THEME
            )
            : '';

        return sprintf(
            '%s:%d/%s%s',
            self::$devServer,
            $this->port,
            $prefix,
            ltrim($entryPath, '/')
        );
    }

    private function renderDevelopment(
        string $entryPath
    ): string {
        $entryUrl = $this->developmentUrl(
            $entryPath
        );

        if ($this->isCssFile($entryPath)) {
            return sprintf(
                '<link rel="stylesheet" href="%s">',
                $this->escape($entryUrl)
            );
        }

        $clientUrl = sprintf(
            '%s:%d/@vite/client',
            self::$devServer,
            $this->port
        );

        return sprintf(
            '<script type="module" src="%s"></script>%s'
            . '<script type="module" src="%s"></script>',
            $this->escape($clientUrl),
            PHP_EOL,
            $this->escape($entryUrl)
        );
    }

    private function renderProduction(
        string $entryPath,
        string $publicPath
    ): string {
        $manifestFile = rtrim(
            (string) (
                $_SERVER['DOCUMENT_ROOT']
                ?? dirname(__DIR__, 2)
            ),
            '/\\'
        )
            . DIRECTORY_SEPARATOR
            . str_replace(
                ['/', '\\'],
                DIRECTORY_SEPARATOR,
                $this->manifestPath()
            );

        if (!is_file($manifestFile)) {
            return '';
        }

        $manifest = json_decode(
            (string) file_get_contents($manifestFile),
            true
        );

        if (
            !is_array($manifest)
            || !isset($manifest[$entryPath])
            || !is_array($manifest[$entryPath])
        ) {
            return '';
        }

        $asset = $manifest[$entryPath];
        $html = [];

        foreach (
            $this->collectCss(
                $asset,
                $manifest
            ) as $cssFile
        ) {
            $html[] = sprintf(
                '<link rel="stylesheet" href="%s">',
                $this->escape(
                    $publicPath . $cssFile
                )
            );
        }

        $file = $asset['file'] ?? null;

        if (is_string($file) && $file !== '') {
            if ($this->isCssFile($file)) {
                /*
                 * CSS entry файловете обикновено
                 * посочват CSS файла директно.
                 */
                $cssUrl = $publicPath . $file;

                if (
                    !in_array(
                        $cssUrl,
                        array_map(
                            fn(string $item): string =>
                            $this->extractHref($item),
                            $html
                        ),
                        true
                    )
                ) {
                    $html[] = sprintf(
                        '<link rel="stylesheet" href="%s">',
                        $this->escape($cssUrl)
                    );
                }
            } else {
                $html[] = sprintf(
                    '<script type="module" src="%s"></script>',
                    $this->escape(
                        $publicPath . $file
                    )
                );
            }
        }

        return implode(PHP_EOL, $html);
    }

    /**
     * @param array<string, mixed> $asset
     * @param array<string, array<string, mixed>> $manifest
     * @return list<string>
     */
    private function collectCss(
        array $asset,
        array $manifest,
        array &$visited = []
    ): array {
        $css = [];

        foreach ($asset['css'] ?? [] as $file) {
            if (is_string($file) && $file !== '') {
                $css[] = $file;
            }
        }

        foreach ($asset['imports'] ?? [] as $import) {
            if (
                !is_string($import)
                || isset($visited[$import])
                || !isset($manifest[$import])
                || !is_array($manifest[$import])
            ) {
                continue;
            }

            $visited[$import] = true;

            $css = array_merge(
                $css,
                $this->collectCss(
                    $manifest[$import],
                    $manifest,
                    $visited
                )
            );
        }

        return array_values(
            array_unique($css)
        );
    }

    private function isDevServerAvailable(): bool
    {
        $handle = @fsockopen(
            'localhost',
            $this->port,
            $errorCode,
            $errorMessage,
            0.1
        );

        if ($handle === false) {
            return false;
        }

        fclose($handle);

        return true;
    }

    private function isCssFile(string $file): bool
    {
        return strtolower(
            pathinfo(
                parse_url($file, PHP_URL_PATH)
                ?: $file,
                PATHINFO_EXTENSION
            )
        ) === 'css';
    }

    private function escape(string $value): string
    {
        return htmlspecialchars(
            $value,
            ENT_QUOTES,
            'UTF-8'
        );
    }

    private function extractHref(string $html): string
    {
        if (
            preg_match(
                '/href="([^"]+)"/',
                $html,
                $matches
            )
        ) {
            return html_entity_decode(
                $matches[1],
                ENT_QUOTES,
                'UTF-8'
            );
        }

        return '';
    }

    private static function normalizeEntryPath(
        string $entryPath
    ): string {
        $entryPath = str_replace(
            '\\',
            '/',
            trim($entryPath)
        );

        if (
            $entryPath === ''
            || str_contains($entryPath, "\0")
            || str_starts_with($entryPath, '/')
            || preg_match(
                '/^[A-Za-z]:\//',
                $entryPath
            )
            || in_array(
                '..',
                explode('/', $entryPath),
                true
            )
        ) {
            throw new InvalidArgumentException(
                'Vite entry path must be a safe relative path.'
            );
        }

        return ltrim($entryPath, './');
    }
}
