<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Pages\Controllers;

use Flex\Core\Assets\AdminAssetRegistry;
use Flex\Core\Assets\ViteAssetResolver;
use Flex\Core\Helpers\Flash;
use Flex\Core\Http\Exceptions\ForbiddenHttpException;
use Flex\Core\Http\RedirectResponse;
use Flex\Core\View\Contracts\ViewRendererInterface;
use Flex\Features\AdminUI\Services\AdminUIRenderer;
use Flex\Features\AdminUI\Configuration\AdminUIConfig;
use Flex\Features\AdminUI\Navigation\SidebarRegistry;
use Flex\Features\AdminUI\Services\AdminUIAssets;
use Flex\Features\Pages\Controllers\PageContentController;
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

final class PageContentControllerTest extends TestCase
{
    private PageRepositoryInterface&MockObject $pages;
    private PageOptionRepositoryInterface&MockObject $options;
    private PageElementRepositoryInterface&MockObject $elements;
    private ViewRendererInterface&MockObject $views;
    private PageContentController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        Flash::pull();

        $this->pages = $this->createMock(PageRepositoryInterface::class);
        $this->options = $this->createMock(PageOptionRepositoryInterface::class);
        $this->elements = $this->createMock(PageElementRepositoryInterface::class);
        $this->views = $this->createMock(ViewRendererInterface::class);

        $config = new AdminUIConfig(
            static fn (string $path, mixed $default = null): mixed => $default
        );
        $sidebars = new SidebarRegistry();
        $sidebars->create(SidebarRegistry::DEFAULT_SIDEBAR, 'Administration');
        $adminUI = new AdminUIRenderer(
            $this->views,
            new AdminUIAssets(
                $config,
                new AdminAssetRegistry(),
                new ViteAssetResolver(
                    manifestPath: __DIR__ . '/missing.json',
                    development: true
                )
            ),
            $config,
            $sidebars
        );
        $assets = new AdminAssetRegistry();

        $this->controller = new PageContentController(
            new PageService($this->pages, new PageTreeService($this->pages)),
            new PageOptionService($this->options),
            new PageElementService($this->elements),
            $adminUI,
            $assets
        );
    }

    public function testItRejectsBuilderWhenPageOptionIsDisabled(): void
    {
        $page = $this->page();
        $this->pages->method('findOrFail')->with(3)->willReturn($page);
        $this->options->method('find')->with($page, 'is_with_page_options')
            ->willReturn(null);
        $this->elements->expects(self::never())->method('allFor');

        $this->expectException(ForbiddenHttpException::class);

        $this->controller->show(3);
    }

    public function testEditRedirectsWithFlashWhenBuilderIsDisabled(): void
    {
        $page = $this->page();
        $this->pages->method('findOrFail')->with(3)->willReturn($page);
        $this->options->method('find')->with($page, 'is_with_page_options')
            ->willReturn(null);
        $this->views->expects(self::never())->method('response');

        $response = $this->controller->edit(3);

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/admin/pages', $response->targetUrl());
        self::assertSame([
            [
                'type' => 'error',
                'message' => 'Page builder-ът не е активиран за тази страница.',
            ],
        ], Flash::pull());
    }

    public function testItReturnsElementsWhenPageOptionIsEnabled(): void
    {
        $page = $this->page();
        $option = new PageOption();
        $option->setRawAttributes([
            'page_id' => 3,
            'option_key' => 'is_with_page_options',
            'option_value' => 'true',
        ], true);

        $this->pages->method('findOrFail')->with(3)->willReturn($page);
        $this->options->method('find')->with($page, 'is_with_page_options')
            ->willReturn($option);
        $this->elements->method('allFor')->with($page)
            ->willReturn(new Collection());

        $response = $this->controller->show(3);

        self::assertSame(['data' => []], json_decode(
            $response->content(),
            true,
            512,
            JSON_THROW_ON_ERROR
        ));
    }

    private function page(): Page
    {
        $page = new Page();
        $page->setRawAttributes([
            'id' => 3,
            'name' => 'Page',
            'deleted_at' => null,
        ], true);
        $page->exists = true;

        return $page;
    }
}
