<?php

declare(strict_types=1);

namespace Flex\Core\Assets;

final class AdminAssetRegistry
{
    /**
     * @var array<string, string>
     */
    private array $scripts = [];

    /**
     * @var array<string, string>
     */
    private array $styles = [];

    public function script(
        string $feature,
        string $asset
    ): self {
        $path = $this->featurePath(
            $feature,
            'js',
            $asset,
            'js'
        );

        $this->scripts[$path] = $path;

        return $this;
    }

    public function style(
        string $feature,
        string $asset
    ): self {
        $path = $this->featurePath(
            $feature,
            'css',
            $asset,
            'css'
        );

        $this->styles[$path] = $path;

        return $this;
    }

    public function scriptPath(
        string $path
    ): self {
        $path = $this->normalizePath($path);

        $this->scripts[$path] = $path;

        return $this;
    }

    public function stylePath(
        string $path
    ): self {
        $path = $this->normalizePath($path);

        $this->styles[$path] = $path;

        return $this;
    }

    /**
     * @return string[]
     */
    public function scripts(): array
    {
        return array_values(
            $this->scripts
        );
    }

    /**
     * @return string[]
     */
    public function styles(): array
    {
        return array_values(
            $this->styles
        );
    }

    public function clear(): void
    {
        $this->scripts = [];
        $this->styles = [];
    }

    private function featurePath(
        string $feature,
        string $type,
        string $asset,
        string $extension
    ): string {
        $feature = trim(
            $feature,
            '/\\'
        );

        $asset = trim(
            $asset,
            '/\\'
        );

        if (
            str_ends_with(
                $asset,
                '.' . $extension
            )
        ) {
            $asset = substr(
                $asset,
                0,
                -strlen('.' . $extension)
            );
        }

        return sprintf(
            'app/Features/%s/Resources/%s/%s.%s',
            $feature,
            $type,
            $asset,
            $extension
        );
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
