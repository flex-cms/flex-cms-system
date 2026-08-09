<?php

declare(strict_types=1);

namespace Tests\Unit\Features\AdminUI\Services;

use Flex\Features\AdminUI\Configuration\AdminUIConfig;
use Flex\Features\AdminUI\Services\AdminUIAssets;
use PHPUnit\Framework\TestCase;

final class AdminUIAssetsTest extends TestCase
{
    private string $documentRoot;

    private string|false $previousDocumentRoot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->previousDocumentRoot =
            $_SERVER['DOCUMENT_ROOT'] ?? false;

        $this->documentRoot =
            sys_get_temp_dir()
            . DIRECTORY_SEPARATOR
            . 'flex-admin-ui-assets-'
            . bin2hex(random_bytes(6));

        $manifestDirectory =
            $this->documentRoot
            . DIRECTORY_SEPARATOR
            . 'public'
            . DIRECTORY_SEPARATOR
            . 'dist'
            . DIRECTORY_SEPARATOR
            . '.vite';

        mkdir(
            $manifestDirectory,
            0777,
            true
        );

        file_put_contents(
            $manifestDirectory
                . DIRECTORY_SEPARATOR
                . 'manifest.json',
            json_encode(
                [
                    'resources/ui/admin/styles/index.css' => [
                        'file' =>
                            'assets/admin-ui.css',
                        'isEntry' => true,
                    ],

                    'resources/ui/admin/index.js' => [
                        'file' =>
                            'assets/admin-ui.js',
                        'isEntry' => true,
                    ],
                ],
                JSON_THROW_ON_ERROR
                | JSON_UNESCAPED_SLASHES
            )
        );

        $_SERVER['DOCUMENT_ROOT'] =
            $this->documentRoot;
    }

    protected function tearDown(): void
    {
        if ($this->previousDocumentRoot === false) {
            unset($_SERVER['DOCUMENT_ROOT']);
        } else {
            $_SERVER['DOCUMENT_ROOT'] =
                $this->previousDocumentRoot;
        }

        $this->deleteDirectory(
            $this->documentRoot
        );

        parent::tearDown();
    }

    public function testItRendersTrackedStylesheet(): void
    {
        $html = $this->assets()->styles();

        self::assertSame(
            '<link data-turbo-track="reload" rel="stylesheet" href="/public/dist/assets/admin-ui.css">',
            $html
        );
    }

    public function testItRendersTrackedJavaScript(): void
    {
        $html = $this->assets()->scripts();

        self::assertSame(
            '<script data-turbo-track="reload" type="module" src="/public/dist/assets/admin-ui.js"></script>',
            $html
        );
    }

    public function testItRendersTurboMetaTags(): void
    {
        $html = $this->assets()->turboMetaTags();

        self::assertStringContainsString(
            '<meta name="turbo-prefetch" content="false">',
            $html
        );

        self::assertStringContainsString(
            '<meta name="view-transition" content="same-origin">',
            $html
        );
    }

    public function testItDoesNotRenderTurboMetaTagsWhenDisabled(): void
    {
        $assets = $this->assets([
            'admin_ui.turbo_enabled' => false,
        ]);

        self::assertSame(
            '',
            $assets->turboMetaTags()
        );
    }

    public function testThemeBootstrapUsesUserPreference(): void
    {
        $html = $this->assets()
            ->themeBootstrap('dark');

        self::assertStringContainsString(
            'const fallbackPreference = "dark";',
            $html
        );

        self::assertStringContainsString(
            'root.dataset.theme = theme;',
            $html
        );

        self::assertStringContainsString(
            'root.dataset.themePreference = preference;',
            $html
        );
    }

    public function testThemeBootstrapFallsBackForInvalidPreference(): void
    {
        $html = $this->assets()
            ->themeBootstrap('invalid-theme');

        self::assertStringContainsString(
            'const fallbackPreference = "system";',
            $html
        );
    }

    public function testThemeBootstrapSafelyHandlesMaliciousPreference(): void
    {
        $html = $this->assets()->themeBootstrap(
            '</script><script>alert(1)</script>'
        );

        self::assertStringNotContainsString(
            'alert(1)',
            $html
        );

        self::assertStringContainsString(
            'const fallbackPreference = "system";',
            $html
        );
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function assets(
        array $overrides = []
    ): AdminUIAssets {
        $values = array_replace(
            [
                /*
                 * Избираме порт, на който не работи
                 * Vite dev server, за да използваме
                 * тестовия production manifest.
                 */
                'admin_ui.vite_port' => 65534,
                'admin_ui.turbo_enabled' => true,
                'admin_ui.default_theme' => 'system',
            ],
            $overrides
        );

        $config = new AdminUIConfig(
            static fn (
                string $path,
                mixed $default = null
            ): mixed =>
                $values[$path] ?? $default
        );

        return new AdminUIAssets($config);
    }

    private function deleteDirectory(
        string $path
    ): void {
        if (!is_dir($path)) {
            return;
        }

        $items = array_diff(
            scandir($path) ?: [],
            ['.', '..']
        );

        foreach ($items as $item) {
            $target = $path
                . DIRECTORY_SEPARATOR
                . $item;

            if (is_dir($target)) {
                $this->deleteDirectory($target);
                continue;
            }

            unlink($target);
        }

        rmdir($path);
    }
}