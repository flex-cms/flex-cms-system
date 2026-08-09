<?php

declare(strict_types=1);

namespace Tests\Unit\View;

use Flex\Core\View\Exceptions\ViewException;
use Flex\Core\View\ViewFinder;
use Flex\Core\View\ViewRenderer;
use PHPUnit\Framework\TestCase;

final class ViewRendererTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/flex-views-' . bin2hex(random_bytes(6));
        mkdir($this->root . '/app/views/layouts', 0777, true);
        mkdir($this->root . '/app/Features/Settings/Views', 0777, true);
        file_put_contents($this->root . '/app/Features/Settings/Views/show.php', '<p><?= htmlspecialchars($group) ?></p>');
        file_put_contents($this->root . '/app/views/layouts/admin.php', '<title><?= $title ?></title><main><?= $content ?></main>');
    }

    protected function tearDown(): void { $this->delete($this->root); }

    public function testItRendersAFeatureViewInsideTheCoreAdminLayout(): void
    {
        $renderer = new ViewRenderer(new ViewFinder($this->root));
        $html = $renderer->render('Settings::show', ['group' => '<general>', 'title' => 'Settings'], 'admin');
        self::assertSame('<title>Settings</title><main><p>&lt;general&gt;</p></main>', $html);
    }

    public function testItReturnsAViewResponse(): void
    {
        $renderer = new ViewRenderer(new ViewFinder($this->root));
        $response = $renderer->response('Settings::show', ['group' => 'general'], 'admin');
        self::assertSame(200, $response->status());
        self::assertSame('text/html; charset=UTF-8', $response->header('Content-Type'));
    }

    public function testItRejectsPathTraversal(): void
    {
        $renderer = new ViewRenderer(new ViewFinder($this->root));
        $this->expectException(ViewException::class);
        $renderer->render('Settings::../secret');
    }

    private function delete(string $path): void
    {
        if (!is_dir($path)) { return; }
        foreach (array_diff(scandir($path) ?: [], ['.', '..']) as $item) {
            $target = $path . '/' . $item;
            is_dir($target) ? $this->delete($target) : unlink($target);
        }
        rmdir($path);
    }
}
