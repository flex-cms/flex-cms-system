<?php

declare(strict_types=1);

namespace Tests\Unit\Routing;

use Flex\Core\Routing\FlexRouter;
use Flex\Core\Routing\RouteCollection;
use Flex\Core\Routing\RouteRegistrar;
use PHPUnit\Framework\TestCase;

final class FlexRouterTest extends TestCase
{
    private RouteCollection $routes;

    protected function setUp(): void
    {
        $this->routes = new RouteCollection();
        FlexRouter::setRegistrar(new RouteRegistrar($this->routes));
    }

    protected function tearDown(): void
    {
        FlexRouter::reset();
    }

    public function testItRegistersRoutesThroughTheFacade(): void
    {
        FlexRouter::get('/users', static fn () => 'users')->name('users.index');

        self::assertCount(1, $this->routes);
        self::assertSame('/users', $this->routes->named('users.index')?->uri());
    }

    public function testItAppliesNestedGroupAttributes(): void
    {
        FlexRouter::prefix('/admin')
            ->name('admin.')
            ->middleware('auth')
            ->group(function (): void {
                FlexRouter::prefix('/users')
                    ->name('users.')
                    ->middleware('admin')
                    ->group(function (): void {
                        FlexRouter::get('/{id}', static fn () => null)
                            ->whereNumber('id')
                            ->name('show');
                    });
            });

        $route = $this->routes->named('admin.users.show');
        self::assertNotNull($route);
        self::assertSame('/admin/users/{id}', $route->uri());
        self::assertSame(['auth', 'admin'], $route->getMiddleware());
    }

    public function testNestedGroupMayRemoveInheritedMiddleware(): void
    {
        FlexRouter::middleware(['auth', 'verified'])->group(function (): void {
            FlexRouter::withoutMiddleware('verified')->group(function (): void {
                FlexRouter::get('/public-profile', static fn () => null);
            });
        });

        self::assertSame(['auth'], $this->routes->all()[0]->getMiddleware());
    }

    public function testMatchRegistersMultipleMethods(): void
    {
        $route = FlexRouter::match(['GET', 'POST'], '/search', static fn () => null);

        self::assertSame(['GET', 'POST', 'HEAD'], $route->methods());
    }
}
