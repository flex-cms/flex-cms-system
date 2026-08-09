<?php

declare(strict_types=1);

namespace Tests\Unit\Features\AdminUI\Configuration;

use Flex\Features\AdminUI\Configuration\AdminUIConfig;
use PHPUnit\Framework\TestCase;

final class AdminUIConfigTest extends TestCase
{
    public function testItReturnsDefaultConfiguration(): void
    {
        $config = new AdminUIConfig(
            static fn(
            string $path,
            mixed $default = null
        ): mixed => $default
        );

        self::assertSame(
            'Flex CMS',
            $config->name()
        );

        self::assertSame(
            'system',
            $config->defaultTheme()
        );

        self::assertTrue(
            $config->turboEnabled()
        );

        self::assertSame(
            3000,
            $config->vitePort()
        );

        self::assertSame(
            ['/admin/settings'],
            $config->turboPaths()
        );
    }

    public function testItReturnsConfiguredValues(): void
    {
        $values = [
            'admin_ui.name' => 'Flex Admin',
            'admin_ui.default_theme' => 'dark',
            'admin_ui.turbo_enabled' => false,
            'admin_ui.vite_port' => 5173,
            'admin_ui.turbo_paths' => [
                '/admin/settings',
                '/admin/profile',
            ],
        ];

        $config = new AdminUIConfig(
            static fn(
            string $path,
            mixed $default = null
        ): mixed =>
            $values[$path] ?? $default
        );

        self::assertSame(
            'Flex Admin',
            $config->name()
        );

        self::assertSame(
            'dark',
            $config->defaultTheme()
        );

        self::assertFalse(
            $config->turboEnabled()
        );

        self::assertSame(
            5173,
            $config->vitePort()
        );

        self::assertSame(
            [
                '/admin/settings',
                '/admin/profile',
            ],
            $config->turboPaths()
        );
    }

    public function testItFallsBackForInvalidTheme(): void
    {
        $config = $this->configWith([
            'admin_ui.default_theme' =>
                'unsupported',
        ]);

        self::assertSame(
            'system',
            $config->defaultTheme()
        );
    }

    public function testItFallsBackForInvalidPort(): void
    {
        self::assertSame(
            3000,
            $this->configWith([
                'admin_ui.vite_port' => 0,
            ])->vitePort()
        );

        self::assertSame(
            3000,
            $this->configWith([
                'admin_ui.vite_port' => 70000,
            ])->vitePort()
        );

        self::assertSame(
            3000,
            $this->configWith([
                'admin_ui.vite_port' => 'invalid',
            ])->vitePort()
        );
    }

    public function testItNormalizesTurboPaths(): void
    {
        $config = $this->configWith([
            'admin_ui.turbo_paths' => [
                'admin/settings/',
                '/admin//settings',
                '/admin/profile?tab=general',
                '',
                null,
            ],
        ]);

        self::assertSame(
            [
                '/admin/settings',
                '/admin/profile',
            ],
            $config->turboPaths()
        );
    }

    public function testItMatchesTurboPathPrefixes(): void
    {
        $config = $this->configWith([
            'admin_ui.turbo_paths' => [
                '/admin/settings',
            ],
        ]);

        self::assertTrue(
            $config->supportsTurboPath(
                '/admin/settings'
            )
        );

        self::assertTrue(
            $config->supportsTurboPath(
                '/admin/settings/general'
            )
        );

        self::assertTrue(
            $config->supportsTurboPath(
                '/admin/settings/mail?tab=smtp'
            )
        );
    }

    public function testItDoesNotMatchSimilarOrLegacyPaths(): void
    {
        $config = $this->configWith([
            'admin_ui.turbo_paths' => [
                '/admin/settings',
            ],
        ]);

        self::assertFalse(
            $config->supportsTurboPath(
                '/admin/setting'
            )
        );

        self::assertFalse(
            $config->supportsTurboPath(
                '/admin/settings-backup'
            )
        );

        self::assertFalse(
            $config->supportsTurboPath(
                '/admin/users'
            )
        );
    }

    /**
     * @param array<string, mixed> $values
     */
    private function configWith(
        array $values
    ): AdminUIConfig {
        return new AdminUIConfig(
            static fn(
            string $path,
            mixed $default = null
        ): mixed =>
            $values[$path] ?? $default
        );
    }
}
