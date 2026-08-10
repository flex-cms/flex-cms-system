<?php

declare(strict_types=1);

namespace Flex\Core\Assets;

use RuntimeException;

final class ViteAssetResolver
{
    /**
     * @var array<string, mixed>|null
     */
    private ?array $manifest = null;

    public function __construct(
        private readonly string $manifestPath,
        private readonly string $baseUrl = '/public/dist/',
        private readonly bool $development = false,
        private readonly string $devServerUrl = 'http://localhost:3000'
    ) {
    }

    public function isDevelopment(): bool
    {
        return $this->development;
    }

    public function asset(
        string $entry
    ): string {
        $entry = $this->normalizePath(
            $entry
        );

        /*
         * Development:
         *
         * Зареждаме директно source файла
         * през Vite dev server.
         */
        if ($this->development) {
            return rtrim(
                $this->devServerUrl,
                '/'
            )
                . '/'
                . ltrim(
                    $entry,
                    '/'
                );
        }

        /*
         * Production:
         *
         * Използваме build manifest.
         */
        $manifest = $this->manifest();

        if (!isset($manifest[$entry])) {
            throw new RuntimeException(
                sprintf(
                    'Vite asset [%s] was not found in the manifest.',
                    $entry
                )
            );
        }

        $file =
            $manifest[$entry]['file']
            ?? null;

        if (
            !is_string($file) ||
            $file === ''
        ) {
            throw new RuntimeException(
                sprintf(
                    'Vite asset [%s] has no output file.',
                    $entry
                )
            );
        }

        return rtrim(
            $this->baseUrl,
            '/'
        )
            . '/'
            . ltrim(
                $file,
                '/'
            );
    }

    /**
     * @return string[]
     */
    public function css(
        string $entry
    ): array {
        /*
         * В development CSS dependencies
         * се управляват от Vite imports.
         */
        if ($this->development) {
            return [];
        }

        $entry =
            $this->normalizePath(
                $entry
            );

        $manifest = $this->manifest();

        if (!isset($manifest[$entry])) {
            return [];
        }

        $css =
            $manifest[$entry]['css']
            ?? [];

        if (!is_array($css)) {
            return [];
        }

        return array_map(
            fn (string $file): string =>
                rtrim(
                    $this->baseUrl,
                    '/'
                )
                . '/'
                . ltrim(
                    $file,
                    '/'
                ),
            $css
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function manifest(): array
    {
        if ($this->manifest !== null) {
            return $this->manifest;
        }

        if (!is_file($this->manifestPath)) {
            throw new RuntimeException(
                sprintf(
                    'Vite manifest not found at [%s].',
                    $this->manifestPath
                )
            );
        }

        $json =
            file_get_contents(
                $this->manifestPath
            );

        if ($json === false) {
            throw new RuntimeException(
                'Unable to read Vite manifest.'
            );
        }

        $manifest =
            json_decode(
                $json,
                true
            );

        if (!is_array($manifest)) {
            throw new RuntimeException(
                'Invalid Vite manifest.'
            );
        }

        return $this->manifest =
            $manifest;
    }

    private function normalizePath(
        string $path
    ): string {
        return str_replace(
            '\\',
            '/',
            ltrim($path, '/')
        );
    }
}
