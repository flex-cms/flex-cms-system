<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Pages\Controllers;

use Flex\Core\Assets\AdminAssetRegistry;
use Flex\Core\Assets\ViteAssetResolver;
use Flex\Core\Http\Request;
use Flex\Core\View\Contracts\ViewRendererInterface;
use Flex\Core\View\ViewResponse;
use Flex\Features\AdminUI\Configuration\AdminUIConfig;
use Flex\Features\AdminUI\Navigation\SidebarRegistry;
use Flex\Features\AdminUI\Services\AdminUIAssets;
use Flex\Features\AdminUI\Services\AdminUIRenderer;
use Flex\Features\Pages\Controllers\PagesController;
use Flex\Features\Pages\Models\Page;
use Flex\Features\Pages\Models\PageOption;
use Flex\Features\Pages\Repositories\PageElementRepositoryInterface;
use Flex\Features\Pages\Repositories\PageOptionRepositoryInterface;
use Flex\Features\Pages\Repositories\PageRepositoryInterface;
use Flex\Features\Pages\Services\PageElementService;
use Flex\Features\Pages\Services\PageOptionService;
use Flex\Features\Pages\Services\PageService;
use Flex\Features\Pages\Services\PageTreeService;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class PagesControllerTest extends TestCase
{
    private PageRepositoryInterface&MockObject $pages;
    private PageOptionRepositoryInterface&MockObject $options;
    private PageElementRepositoryInterface&MockObject $elements;
    private ViewRendererInterface&MockObject $views;
    private PagesController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pages = $this->createMock(PageRepositoryInterface::class);
        $this->options = $this->createMock(PageOptionRepositoryInterface::class);
        $this->elements = $this->createMock(PageElementRepositoryInterface::class);
        $this->views = $this->createMock(ViewRendererInterface::class);
        $sidebars = new SidebarRegistry();
        $sidebars->create(SidebarRegistry::DEFAULT_SIDEBAR, 'Administration');
        $config = new AdminUIConfig(
            static fn (string $path, mixed $default = null): mixed => $default
        );
        $assetRegistry = new AdminAssetRegistry();
        $adminUI = new AdminUIRenderer(
            $this->views,
            new AdminUIAssets(
                $config,
                $assetRegistry,
                new ViteAssetResolver(
                    manifestPath: __DIR__ . '/missing.json',
                    development: true
                )
            ),
            $config,
            $sidebars
        );
        $tree = new PageTreeService($this->pages);

        $this->controller = new PagesController(
            new PageService($this->pages, $tree),
            new PageOptionService($this->options),
            new PageElementService($this->elements),
            $adminUI,
            $assetRegistry
        );
    }

    public function testIndexRendersApiBackedPagesView(): void
    {
        $this->pages
            ->expects(self::never())
            ->method('all');

        $expected = new ViewResponse('<h1>Pages</h1>');
        $this->views
            ->expects(self::once())
            ->method('response')
            ->with(
                'Pages::index',
                self::callback(static fn (array $data): bool =>
                    $data['title'] === 'Управление на страници'
                    && $data['primaryButton']['url'] === '/admin/pages/create'
                    && isset($data['adminUIConfig'], $data['adminUISidebar'])
                ),
                AdminUIRenderer::LAYOUT,
                200
            )
            ->willReturn($expected);

        self::assertSame($expected, $this->controller->index(new Request(
            method: 'GET',
            uri: '/admin/pages?search=landing&status=active',
            query: ['search' => ' landing ', 'status' => 'active']
        )));
    }

    public function testApiIndexPassesStatusFilterToPageService(): void
    {
        $result = [
            'data' => [],
            'pagination' => [
                'page' => 1,
                'per_page' => 25,
                'total' => 0,
                'last_page' => 1,
            ],
        ];

        $this->pages->expects(self::once())
            ->method('paginate')
            ->with(
                1,
                25,
                'position',
                'asc',
                'home',
                ['status' => 'inactive']
            )
            ->willReturn($result);

        $response = $this->controller->apiIndex(new Request(
            method: 'GET',
            uri: '/api/admin/pages',
            query: [
                'page' => '1',
                'per_page' => '25',
                'sort' => 'position',
                'direction' => 'asc',
                'search' => 'home',
                'filter' => ['status' => 'inactive'],
            ]
        ));

        self::assertSame($result, json_decode(
            $response->content(),
            true,
            512,
            JSON_THROW_ON_ERROR
        ));
    }

    public function testReorderRejectsNonListPayload(): void
    {
        $this->pages->expects(self::never())->method('transaction');

        $response = $this->controller->reorder(new Request(
            method: 'POST',
            uri: '/admin/pages/reorder',
            body: ['positions' => ['id' => 1, 'position' => 0]]
        ));

        self::assertSame(422, $response->status());
        self::assertFalse(json_decode(
            $response->content(),
            true,
            512,
            JSON_THROW_ON_ERROR
        )['success']);
    }

    public function testToggleReturnsJsonForJavaScriptTableAction(): void
    {
        $page = new Page();
        $page->setRawAttributes([
            'id' => 5,
            'is_active' => false,
            'deleted_at' => null,
        ], true);
        $page->exists = true;

        $activated = clone $page;
        $activated->is_active = true;

        $this->pages->expects(self::once())
            ->method('findOrFail')
            ->with(5)
            ->willReturn($page);
        $this->pages->expects(self::once())
            ->method('setActive')
            ->with($page, true)
            ->willReturn($activated);

        $response = $this->controller->toggle(5);
        $data = json_decode(
            $response->content(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        self::assertSame(200, $response->status());
        self::assertTrue($data['success']);
        self::assertTrue($data['is_active']);
    }

    public function testStorePassesRequestToRelatedDataServices(): void
    {
        $page = new Page();
        $page->setRawAttributes([
            'id' => 7,
            'name' => 'Начало',
            'slug' => 'nachalo',
            'full_slug' => 'nachalo',
            'parent_id' => null,
            'position' => 0,
            'is_active' => true,
        ], true);
        $page->exists = true;

        $this->pages->method('transaction')->willReturnCallback(
            static fn (callable $callback): mixed => $callback()
        );
        $this->pages->expects(self::once())
            ->method('slugExists')
            ->with('nachalo', null, null)
            ->willReturn(false);
        $this->pages->expects(self::once())
            ->method('create')
            ->willReturn($page);

        $this->options->expects(self::once())
            ->method('transaction')
            ->willReturnCallback(static fn (callable $callback): mixed => $callback());
        $this->options->expects(self::exactly(3))
            ->method('save')
            ->willReturn(new PageOption());

        $this->elements->expects(self::once())
            ->method('transaction')
            ->willReturnCallback(static fn (callable $callback): mixed => $callback());
        $this->elements->expects(self::once())
            ->method('deleteMissing')
            ->with($page, [])
            ->willReturn(0);
        $this->elements->expects(self::once())
            ->method('allFor')
            ->with($page)
            ->willReturn(new Collection());

        $response = $this->controller->store(new Request(
            method: 'POST',
            uri: '/admin/pages/store',
            body: [
                'name' => 'Начало',
                'slug' => 'nachalo',
                'parent_id' => '',
                'position' => '0',
                'is_active' => '1',
                'options' => [
                    'excerpt' => 'Начална страница',
                    'use_full_slug' => '1',
                ],
                'elements_json' => '[]',
            ]
        ));

        self::assertSame(200, $response->status());

        $data = json_decode(
            $response->content(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        self::assertTrue($data['success']);
        self::assertSame(7, $data['id']);
        self::assertSame('/admin/pages/edit/7', $data['redirect']);
    }
}
