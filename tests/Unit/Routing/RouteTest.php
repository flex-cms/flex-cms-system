<?php

declare(strict_types=1);

namespace Tests\Unit\Routing;

use Flex\Core\Routing\Exceptions\InvalidRouteException;
use Flex\Core\Routing\Route;
use PHPUnit\Framework\TestCase;

final class RouteTest extends TestCase
{
    public function testGetAutomaticallySupportsHead(): void
    {
        $route = new Route('GET', '/users', static fn () => null);

        self::assertSame(['GET', 'HEAD'], $route->methods());
    }

    public function testItCompilesConstraintsForFastRoute(): void
    {
        $route = (new Route('GET', '/users/{id}', static fn () => null))
            ->whereNumber('id');

        self::assertSame('/users/{id:[0-9]+}', $route->fastRoutePattern());
    }

    public function testItCompilesTrailingOptionalParameters(): void
    {
        $route = new Route('GET', '/archive/{year}/{month?}', static fn () => null);

        self::assertSame('/archive/{year}[/{month}]', $route->fastRoutePattern());
    }

    public function testItNestsMultipleTrailingOptionalParameters(): void
    {
        $route = new Route('GET', '/archive/{year?}/{month?}', static fn () => null);

        self::assertSame('/archive[/{year}[/{month}]]', $route->fastRoutePattern());
    }

    public function testOptionalParametersMustBeTrailing(): void
    {
        $route = new Route('GET', '/archive/{month?}/details', static fn () => null);

        $this->expectException(InvalidRouteException::class);
        $route->fastRoutePattern();
    }

    public function testItStoresUniqueMiddleware(): void
    {
        $route = (new Route('POST', '/users', static fn () => null))
            ->middleware('auth', ['admin', 'auth']);

        self::assertSame(['auth', 'admin'], $route->getMiddleware());
    }

    public function testWhereInEscapesValues(): void
    {
        $route = (new Route('GET', '/status/{status}', static fn () => null))
            ->whereIn('status', ['draft', 'in.review']);

        self::assertSame('/status/{status:draft|in\\.review}', $route->fastRoutePattern());
    }
}
