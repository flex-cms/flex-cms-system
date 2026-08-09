<?php

declare(strict_types=1);

namespace Tests\Unit\Routing;

use Flex\Core\Routing\Exceptions\DuplicateRouteException;
use Flex\Core\Routing\Exceptions\DuplicateRouteNameException;
use Flex\Core\Routing\Route;
use Flex\Core\Routing\RouteCollection;
use PHPUnit\Framework\TestCase;

final class RouteCollectionTest extends TestCase
{
    public function testItStoresAndFindsNamedRoutes(): void
    {
        $collection = new RouteCollection();
        $route = (new Route('GET', '/users', static fn () => null))->name('users.index');
        $collection->add($route);

        self::assertSame($route, $collection->named('users.index'));
        self::assertCount(1, $collection);
    }

    public function testItRejectsDuplicateRoutes(): void
    {
        $collection = new RouteCollection();
        $collection->add(new Route('POST', '/users', static fn () => null));

        $this->expectException(DuplicateRouteException::class);
        $collection->add(new Route('POST', '/users', static fn () => null));
    }

    public function testItRejectsDuplicateNames(): void
    {
        $collection = new RouteCollection();
        $collection->add((new Route('GET', '/users', static fn () => null))->name('users.index'));

        $this->expectException(DuplicateRouteNameException::class);
        $collection->add((new Route('GET', '/members', static fn () => null))->name('users.index'));
    }

    public function testRoutesWithDifferentMethodsMayShareAPath(): void
    {
        $collection = new RouteCollection();
        $collection->add(new Route('GET', '/users', static fn () => null));
        $collection->add(new Route('POST', '/users', static fn () => null));

        self::assertCount(2, $collection);
    }

    public function testOverlappingMethodSetsAreRejected(): void
    {
        $collection = new RouteCollection();
        $collection->add(new Route(['GET', 'POST'], '/users', static fn () => null));

        $this->expectException(DuplicateRouteException::class);
        $collection->add(new Route('POST', '/users', static fn () => null));
    }
}
