<?php

declare(strict_types=1);

namespace Tests\Unit\Routing;

use Flex\Core\Routing\FlexRouter;
use Flex\Core\Routing\RouteCollection;
use Flex\Core\Routing\RouteRegistrar;
use PHPUnit\Framework\TestCase;

final class ResourceRegistrationTest extends TestCase
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

    public function testResourceRegistersSevenRoutes(): void
    {
        FlexRouter::resource('/users', ResourceController::class)->register();

        self::assertCount(7, $this->routes);
        self::assertSame('/users/create', $this->routes->named('users.create')?->uri());
        self::assertSame('/users/{user}/edit', $this->routes->named('users.edit')?->uri());
    }

    public function testApiResourceOmitsCreateAndEdit(): void
    {
        FlexRouter::apiResource('/users', ResourceController::class)->register();

        self::assertCount(5, $this->routes);
        self::assertFalse($this->routes->hasNamed('users.create'));
        self::assertFalse($this->routes->hasNamed('users.edit'));
    }

    public function testOnlyAndExceptFilterActions(): void
    {
        FlexRouter::resource('/users', ResourceController::class)
            ->only(['index', 'show', 'destroy'])
            ->except(['show'])
            ->register();

        self::assertCount(2, $this->routes);
        self::assertTrue($this->routes->hasNamed('users.index'));
        self::assertTrue($this->routes->hasNamed('users.destroy'));
    }

    public function testCustomParameterNamesAndMiddlewareAreApplied(): void
    {
        FlexRouter::resource('/people', ResourceController::class)
            ->parameter('person')
            ->middleware('auth')
            ->names(['show' => 'people.profile'])
            ->only(['show'])
            ->register();

        $route = $this->routes->named('people.profile');
        self::assertSame('/people/{person}', $route?->uri());
        self::assertSame(['auth'], $route?->getMiddleware());
    }
}

final class ResourceController
{
    public function index(): void {}
    public function create(): void {}
    public function store(): void {}
    public function show(): void {}
    public function edit(): void {}
    public function update(): void {}
    public function destroy(): void {}
}
