<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Pages\Views;

use Flex\Core\View\ViewFinder;
use Flex\Core\View\ViewRenderer;
use Flex\Features\Pages\Data\PageElementNode;
use Flex\Features\Pages\Data\PageTreeItem;
use Flex\Features\Pages\Models\Page;
use Flex\Features\Pages\Models\PageElement;
use PHPUnit\Framework\TestCase;

final class PagesViewsTest extends TestCase
{
    private ViewRenderer $views;

    protected function setUp(): void
    {
        parent::setUp();

        $this->views = new ViewRenderer(
            new ViewFinder(dirname(__DIR__, 5))
        );
    }

    public function testIndexRendersApiBackedDataTableWithoutEmbeddedRows(): void
    {
        $root = $this->page(
            1,
            '<script>alert(1)</script>',
            'root',
            'root'
        );
        $child = $this->page(
            2,
            'Child',
            'child',
            'root/child',
            1
        );

        $html = $this->views->render('Pages::index', [
            'pages' => [
                new PageTreeItem($root, 0),
                new PageTreeItem($child, 1),
            ],
            'search' => '<query>',
            'status' => 'active',
        ]);

        self::assertStringContainsString('id="pages-table"', $html);
        self::assertStringContainsString('href="/admin/pages/create"', $html);
        self::assertStringNotContainsString('pages-table-data', $html);
        self::assertStringNotContainsString('<script', $html);
        self::assertStringNotContainsString('Child', $html);
    }

    public function testCreateViewRendersEmptyPageForm(): void
    {
        $html = $this->views->render('Pages::create', [
            'page' => null,
            'parentPages' => [],
            'options' => [],
            'elements' => [],
            'templates' => [],
        ]);

        self::assertStringContainsString('action="/admin/pages/store"', $html);
        self::assertStringContainsString('mode="api"', $html);
        self::assertStringContainsString('name="name"', $html);
        self::assertStringContainsString('name="elements_json"', $html);
        self::assertStringContainsString('<flex-dropdown', $html);
        self::assertStringContainsString('name="parent_id"', $html);
        self::assertStringContainsString('name="options[page_template]"', $html);
        self::assertStringNotContainsString('<select', $html);
        self::assertStringNotContainsString('type="file"', $html);
        self::assertStringNotContainsString('<textarea', $html);
        self::assertStringContainsString('Създай страница', $html);
    }

    public function testEditViewRendersCurrentDataAndElementJson(): void
    {
        $parent = $this->page(1, 'Parent', 'parent', 'parent');
        $page = $this->page(2, 'Child', 'child', 'parent/child', 1);
        $element = new PageElement();
        $element->setRawAttributes([
            'id' => 8,
            'page_id' => 2,
            'parent_id' => null,
            'element_type' => 'text',
            'position' => 0,
            'settings' => json_encode([
                'content' => '</textarea><script>alert(1)</script>',
            ], JSON_THROW_ON_ERROR),
        ], true);

        $html = $this->views->render('Pages::edit', [
            'page' => $page,
            'parentPages' => [new PageTreeItem($parent, 0)],
            'options' => [
                'excerpt' => 'Short description',
                'page_template' => 'landing',
                'use_full_slug' => true,
            ],
            'elements' => [new PageElementNode($element)],
            'templates' => ['landing' => 'Landing page'],
        ]);

        self::assertStringContainsString('action="/admin/pages/update/2"', $html);
        self::assertStringContainsString('value="Child"', $html);
        self::assertStringContainsString('value="1"', $html);
        self::assertStringContainsString('Landing page', $html);
        self::assertStringContainsString('Short description', $html);
        self::assertStringContainsString('&lt;/textarea&gt;', $html);
        self::assertStringNotContainsString('</textarea><script>alert(1)</script>', $html);
    }

    private function page(
        int $id,
        string $name,
        string $slug,
        string $fullSlug,
        ?int $parentId = null
    ): Page {
        $page = new Page();
        $page->setRawAttributes([
            'id' => $id,
            'name' => $name,
            'slug' => $slug,
            'full_slug' => $fullSlug,
            'parent_id' => $parentId,
            'position' => 0,
            'is_active' => true,
            'deleted_at' => null,
        ], true);
        $page->exists = true;

        return $page;
    }
}
