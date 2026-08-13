<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Pages\Routing;

use Flex\Core\Routing\FlexRouter;
use Flex\Core\Routing\Route;
use Flex\Core\Routing\RouteCollection;
use Flex\Core\Routing\RouteMatcher;
use Flex\Core\Routing\RouteRegistrar;
use Flex\Features\Pages\Controllers\PagesController;
use Flex\Features\Pages\Controllers\PageContentController;
use Flex\Features\Pages\Controllers\PageFieldsController;
use PHPUnit\Framework\TestCase;

final class PagesRoutesTest extends TestCase
{
    private RouteCollection $routes;

    protected function setUp(): void
    {
        parent::setUp();

        $this->routes = new RouteCollection();
        FlexRouter::setRegistrar(new RouteRegistrar($this->routes));

        require dirname(__DIR__, 5)
            . '/app/Features/Pages/Routes/admin.php';
    }

    protected function tearDown(): void
    {
        FlexRouter::reset();
        parent::tearDown();
    }

    public function testItRegistersAllAdminPageRoutes(): void
    {
        self::assertCount(18, $this->routes);

        foreach ([
            'index', 'create', 'store', 'edit', 'update',
            'toggle', 'delete', 'restore', 'force-delete', 'reorder',
        ] as $name) {
            self::assertTrue($this->routes->hasNamed('admin.pages.' . $name));
        }

        self::assertTrue($this->routes->hasNamed('admin.pages.content.edit'));

        foreach ([
            'index', 'create', 'store', 'import-form', 'import', 'edit', 'update',
        ] as $name) {
            self::assertTrue($this->routes->hasNamed('admin.pages.fields.' . $name));
        }
    }

    public function testRoutesUseAdminMiddlewareAndPagesController(): void
    {
        foreach ($this->routes as $route) {
            self::assertSame(['auth', 'admin'], $route->getMiddleware());
            self::assertContains(
                $route->action()[0],
                [PagesController::class, PageContentController::class, PageFieldsController::class]
            );
        }
    }

    public function testContentEditorRouteUsesNumericPageId(): void
    {
        $route = $this->route('admin.pages.content.edit');

        self::assertSame('/admin/pages/{id}/content', $route->uri());
        self::assertSame(['id' => '[0-9]+'], $route->constraints());
        self::assertSame(
            [PageContentController::class, 'edit'],
            $route->action()
        );
    }

    public function testEditRouteHasNumericIdConstraint(): void
    {
        $route = $this->route('admin.pages.edit');

        self::assertSame('/admin/pages/edit/{id}', $route->uri());
        self::assertSame(['id' => '[0-9]+'], $route->constraints());
        self::assertSame([PagesController::class, 'edit'], $route->action());

        $matcher = new RouteMatcher($this->routes);
        self::assertTrue(
            $matcher->matchMethodAndPath('GET', '/admin/pages/edit/12')->isFound()
        );
        self::assertTrue(
            $matcher->matchMethodAndPath('GET', '/admin/pages/edit/not-a-number')->isNotFound()
        );
    }

    public function testMutationRoutesRejectGetRequests(): void
    {
        $matcher = new RouteMatcher($this->routes);

        foreach ([
            '/admin/pages/store',
            '/admin/pages/update/3',
            '/admin/pages/3/toggle',
            '/admin/pages/3/delete',
            '/admin/pages/3/restore',
            '/admin/pages/3/force-delete',
            '/admin/pages/reorder',
            '/admin/pages/3/fields/store',
            '/admin/pages/3/fields/4/update',
        ] as $path) {
            self::assertTrue(
                $matcher->matchMethodAndPath('GET', $path)->isMethodNotAllowed(),
                $path
            );
        }
    }

    private function route(string $name): Route
    {
        $route = $this->routes->named($name);
        self::assertNotNull($route);

        return $route;
    }
}
