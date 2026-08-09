<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use Flex\Core\Vite;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ViteTest extends TestCase
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
            . 'flex-vite-'
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
                $this->manifest(),
                JSON_THROW_ON_ERROR
                | JSON_PRETTY_PRINT
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

    public function testLegacyJavaScriptEntryStillWorks(): void
    {
        $html = (string) Vite::use('admin')
            ->port(65534);

        self::assertStringContainsString(
            '<link rel="stylesheet" href="/public/dist/assets/admin.css">',
            $html
        );

        self::assertStringContainsString(
            '<script type="module" src="/public/dist/assets/admin.js"></script>',
            $html
        );
    }

    public function testItRendersExplicitJavaScriptEntry(): void
    {
        $html = (string) Vite::entry(
            'resources/ui/admin/index.js'
        )->port(65534);

        self::assertStringContainsString(
            '<link rel="stylesheet" href="/public/dist/assets/shared.css">',
            $html
        );

        self::assertStringContainsString(
            '<script type="module" src="/public/dist/assets/admin-ui.js"></script>',
            $html
        );
    }

    public function testItRendersExplicitCssEntryAsStylesheet(): void
    {
        $html = (string) Vite::entry(
            'resources/ui/admin/styles/index.css'
        )->port(65534);

        self::assertSame(
            '<link rel="stylesheet" href="/public/dist/assets/admin-ui.css">',
            $html
        );

        self::assertStringNotContainsString(
            '<script',
            $html
        );
    }

    public function testItCollectsCssFromImportedChunks(): void
    {
        $html = (string) Vite::entry(
            'resources/ui/admin/index.js'
        )->port(65534);

        self::assertStringContainsString(
            '/public/dist/assets/shared.css',
            $html
        );

        self::assertStringContainsString(
            '/public/dist/assets/components.css',
            $html
        );
    }

    public function testItReturnsEmptyStringForMissingEntry(): void
    {
        $html = (string) Vite::entry(
            'resources/ui/admin/missing.js'
        )->port(65534);

        self::assertSame('', $html);
    }

    public function testItRejectsAbsoluteEntryPath(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        Vite::entry(
            '/resources/ui/admin/index.js'
        );
    }

    public function testItRejectsDirectoryTraversal(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        Vite::entry(
            'resources/ui/../../../secret.js'
        );
    }

    public function testItRejectsInvalidPort(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        Vite::entry(
            'resources/ui/admin/index.js'
        )->port(70000);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function manifest(): array
    {
        return [
            'resources/js/admin.js' => [
                'file' => 'assets/admin.js',
                'isEntry' => true,
                'css' => [
                    'assets/admin.css',
                ],
            ],

            'resources/ui/admin/index.js' => [
                'file' => 'assets/admin-ui.js',
                'isEntry' => true,
                'imports' => [
                    '_shared.js',
                ],
            ],

            'resources/ui/admin/styles/index.css' => [
                'file' => 'assets/admin-ui.css',
                'isEntry' => true,
            ],

            '_shared.js' => [
                'file' => 'assets/shared.js',
                'css' => [
                    'assets/shared.css',
                ],
                'imports' => [
                    '_components.js',
                ],
            ],

            '_components.js' => [
                'file' => 'assets/components.js',
                'css' => [
                    'assets/components.css',
                ],
            ],
        ];
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
