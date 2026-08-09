<?php

declare(strict_types=1);

namespace Flex\Features\AdminUI\Configuration;

use Closure;

final class AdminUIConfig
{
    /**
     * @var Closure(string, mixed): mixed
     */
    private Closure $configurationReader;

    public function __construct(
        ?callable $configurationReader = null
    ) {
        $this->configurationReader =
            $configurationReader !== null
            ? Closure::fromCallable(
                $configurationReader
            )
            : static function (string $path, mixed $default = null): mixed {
                if (!function_exists('core_info')) {
                    return $default;
                }

                return \core_info(
                    $path,
                    $default
                );
            };
    }

    public function name(): string
    {
        return $this->nonEmptyString(
            'admin_ui.name',
            'Flex CMS'
        );
    }

    public function defaultTheme(): string
    {
        $theme = $this->nonEmptyString(
            'admin_ui.default_theme',
            'system'
        );

        return in_array(
            $theme,
            ['light', 'dark', 'system'],
            true
        ) ? $theme : 'system';
    }

    public function turboEnabled(): bool
    {
        return (bool) $this->read(
            'admin_ui.turbo_enabled',
            true
        );
    }

    public function vitePort(): int
    {
        $port = filter_var(
            $this->read(
                'admin_ui.vite_port',
                3000
            ),
            FILTER_VALIDATE_INT
        );

        if (
            $port === false
            || $port < 1
            || $port > 65535
        ) {
            return 3000;
        }

        return $port;
    }

    /**
     * По време на миграцията Turbo работи само
     * за изрично разрешените admin пътища.
     *
     * @return list<string>
     */
    public function turboPaths(): array
    {
        $paths = $this->read(
            'admin_ui.turbo_paths',
            [
                '/admin/settings',
            ]
        );

        if (!is_array($paths)) {
            return [
                '/admin/settings',
            ];
        }

        $normalized = [];

        foreach ($paths as $path) {
            if (!is_string($path)) {
                continue;
            }

            $path = $this->normalizePath($path);

            if ($path !== null) {
                $normalized[] = $path;
            }
        }

        return array_values(
            array_unique($normalized)
        );
    }

    public function supportsTurboPath(
        string $path
    ): bool {
        $path = parse_url(
            $path,
            PHP_URL_PATH
        );

        if (!is_string($path)) {
            return false;
        }

        $path = '/'
            . trim($path, '/');

        foreach ($this->turboPaths() as $prefix) {
            if (
                $path === $prefix
                || str_starts_with(
                    $path,
                    $prefix . '/'
                )
            ) {
                return true;
            }
        }

        return false;
    }

    private function nonEmptyString(
        string $path,
        string $default
    ): string {
        $value = $this->read(
            $path,
            $default
        );

        if (!is_string($value)) {
            return $default;
        }

        $value = trim($value);

        return $value !== ''
            ? $value
            : $default;
    }

    private function normalizePath(
        string $path
    ): ?string {
        $path = parse_url(
            trim($path),
            PHP_URL_PATH
        );

        if (!is_string($path)) {
            return null;
        }

        $path = '/'
            . trim(
                preg_replace(
                    '#/+#',
                    '/',
                    $path
                ) ?? '',
                '/'
            );

        return $path !== '/'
            ? rtrim($path, '/')
            : null;
    }

    private function read(
        string $path,
        mixed $default = null
    ): mixed {
        return ($this->configurationReader)(
            $path,
            $default
        );
    }
}
