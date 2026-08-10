<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Dashboard\Routing;

use Flex\Core\Routing\FlexRouter;
use Flex\Core\Routing\RouteCollection;
use Flex\Core\Routing\RouteRegistrar;
use Flex\Core\Routing\RouteMatcher;
use PHPUnit\Framework\TestCase;

final class DashboardRoutesTest extends TestCase
{
    private RouteCollection $routes;

    protected function setUp(): void
    {
        parent::setUp();
        $this->routes = new RouteCollection();
        FlexRouter::setRegistrar(new RouteRegistrar($this->routes));
        require dirname(__DIR__, 5) . '/app/Features/Dashboard/Routes/admin.php';
    }

    protected function tearDown(): void
    {
        FlexRouter::reset();
        parent::tearDown();
    }

    public function testItRegistersAndMatchesPreviewRoute(): void
    {
        $route = $this->routes->named('admin.dashboard.preview');
        self::assertNotNull($route);
        self::assertSame(['auth', 'admin'], $route->getMiddleware());

        $result = (new RouteMatcher($this->routes))
            ->matchMethodAndPath('GET', '/admin/dashboard-preview');

        self::assertTrue($result->isFound());
        self::assertSame('admin.dashboard.preview', $result->route()->getName());
    }
}
