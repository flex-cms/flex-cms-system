<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Pages\Views;

use Flex\Core\View\ViewFinder;
use Flex\Core\View\ViewRenderer;
use Flex\Features\Pages\Data\PageTreeItem;
use Flex\Features\Pages\Data\PageFieldType;
use Flex\Features\Pages\Models\Page;
use Flex\Features\Pages\Models\PageField;
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
        self::assertStringNotContainsString('name="elements_json"', $html);
        self::assertStringContainsString('<flex-dropdown', $html);
        self::assertStringContainsString('name="parent_id"', $html);
        self::assertStringContainsString('name="options[page_template]"', $html);
        self::assertStringNotContainsString('<select', $html);
        self::assertStringNotContainsString('type="file"', $html);
        self::assertStringNotContainsString('<textarea', $html);
        self::assertStringContainsString('Създай страница', $html);
    }

    public function testEditViewRendersCurrentPageDataWithoutContentEditor(): void
    {
        $parent = $this->page(1, 'Parent', 'parent', 'parent');
        $page = $this->page(2, 'Child', 'child', 'parent/child', 1);
        $html = $this->views->render('Pages::edit', [
            'page' => $page,
            'parentPages' => [new PageTreeItem($parent, 0)],
            'options' => [
                'excerpt' => 'Short description',
                'page_template' => 'landing',
                'use_full_slug' => true,
            ],
            'templates' => ['landing' => 'Landing page'],
        ]);

        self::assertStringContainsString('action="/admin/pages/update/2"', $html);
        self::assertStringContainsString('value="Child"', $html);
        self::assertStringContainsString('value="1"', $html);
        self::assertStringContainsString('Landing page', $html);
        self::assertStringContainsString('Short description', $html);
        self::assertStringNotContainsString('elements_json', $html);
        self::assertStringNotContainsString('Структура на съдържанието', $html);
    }

    public function testContentEditorViewRendersPageContextAndActions(): void
    {
        $page = $this->page(12, '<Page>', 'page', 'page');

        $html = $this->views->render('Pages::content/edit', [
            'page' => $page,
        ]);

        self::assertStringContainsString('id="page-content-builder"', $html);
        self::assertStringContainsString('data-page-id="12"', $html);
        self::assertStringContainsString('/admin/pages/edit/12', $html);
        self::assertStringContainsString('id="page-content-save"', $html);
        self::assertStringContainsString('„&lt;Page&gt;“', $html);
        self::assertStringNotContainsString('„<Page>“', $html);
    }

    public function testFieldsIndexRendersApiBackedTableAndActions(): void
    {
        $page = $this->page(12, '<Page>', 'page', 'page');

        $html = $this->views->render('Pages::fields/index', [
            'page' => $page,
        ]);

        self::assertStringContainsString('id="page-fields-table"', $html);
        self::assertStringContainsString('data-page-id="12"', $html);
        self::assertStringContainsString('/admin/pages/12/fields/create', $html);
        self::assertStringContainsString('/admin/pages/12/fields/import', $html);
        self::assertStringContainsString('„&lt;Page&gt;“', $html);
        self::assertStringNotContainsString('pages-fields-data', $html);
    }

    public function testCreateFieldViewRendersSupportedFieldContract(): void
    {
        $page = $this->page(12, 'Page', 'page', 'page');

        $html = $this->views->render('Pages::fields/create', [
            'page' => $page,
            'field' => null,
            'types' => PageFieldType::options(),
        ]);

        self::assertStringContainsString(
            'action="/admin/pages/12/fields/store"',
            $html
        );
        self::assertStringContainsString('mode="api"', $html);
        self::assertStringContainsString('name="type"', $html);
        self::assertStringContainsString('name="label"', $html);
        self::assertStringContainsString('name="key"', $html);
        self::assertStringContainsString('name="group"', $html);
        self::assertStringContainsString('name="order"', $html);
        self::assertStringContainsString('name="hint"', $html);
        self::assertStringContainsString('Визуален редактор', $html);
        self::assertStringNotContainsString('<textarea', $html);
    }

    public function testEditFieldViewRendersCurrentValues(): void
    {
        $page = $this->page(12, 'Page', 'page', 'page');
        $field = new PageField([
            'type' => PageFieldType::Gallery,
            'label' => '<Gallery>',
            'field_key' => 'gallery',
            'field_group' => 'media',
            'position' => 20,
            'hint' => 'Images',
        ]);
        $field->setAttribute('id', 7);

        $html = $this->views->render('Pages::fields/edit', [
            'page' => $page,
            'field' => $field,
            'types' => PageFieldType::options(),
        ]);

        self::assertStringContainsString(
            'action="/admin/pages/12/fields/7/update"',
            $html
        );
        self::assertStringContainsString('value="gallery"', $html);
        self::assertStringContainsString('value="&lt;Gallery&gt;"', $html);
        self::assertStringContainsString('value="media"', $html);
        self::assertStringContainsString('value="20"', $html);
        self::assertStringContainsString('value="Images"', $html);
        self::assertStringNotContainsString('value="<Gallery>"', $html);
    }

    public function testFieldsImportViewUsesJsonApiForm(): void
    {
        $page = $this->page(12, '<Page>', 'page', 'page');

        $html = $this->views->render('Pages::fields/import', [
            'page' => $page,
        ]);

        self::assertStringContainsString(
            'action="/admin/pages/12/fields/import"',
            $html
        );
        self::assertStringContainsString('id="page-fields-import-form"', $html);
        self::assertStringContainsString('name="fields_json"', $html);
        self::assertStringContainsString('mode="api"', $html);
        self::assertStringNotContainsString('type="file"', $html);
        self::assertStringContainsString('„&lt;Page&gt;“', $html);
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
