<?php

declare(strict_types=1);

namespace Tests\Unit\Routing;

use Flex\Core\Http\Request;
use Flex\Core\Routing\Route;
use Flex\Core\Routing\RouteCollection;
use Flex\Core\Routing\RouteMatcher;
use PHPUnit\Framework\TestCase;

final class RouteMatcherTest extends TestCase
{
    public function testItMatchesAStaticRoute(): void
    {
        $route = new Route('GET', '/users', static fn () => null);
        $matcher = $this->matcherWith($route);

        $result = $matcher->match(new Request('GET', '/users'));

        self::assertTrue($result->isFound());
        self::assertSame($route, $result->route());
    }

    public function testItReturnsNamedParameters(): void
    {
        $route = (new Route('GET', '/users/{id}', static fn () => null))->whereNumber('id');
        $matcher = $this->matcherWith($route);

        $result = $matcher->matchMethodAndPath('GET', '/users/42?tab=profile');

        self::assertTrue($result->isFound());
        self::assertSame('42', $result->parameter('id'));
    }

    public function testItRejectsAParameterThatDoesNotMeetTheConstraint(): void
    {
        $route = (new Route('GET', '/users/{id}', static fn () => null))->whereNumber('id');
        $matcher = $this->matcherWith($route);

        self::assertTrue($matcher->matchMethodAndPath('GET', '/users/not-a-number')->isNotFound());
    }

    public function testItReportsAllowedMethods(): void
    {
        $matcher = $this->matcherWith(new Route(['GET', 'POST'], '/users', static fn () => null));

        $result = $matcher->matchMethodAndPath('DELETE', '/users');

        self::assertTrue($result->isMethodNotAllowed());
        self::assertSame(['GET', 'HEAD', 'POST'], $result->allowedMethods());
    }

    public function testItMatchesAnOptionalParameter(): void
    {
        $matcher = $this->matcherWith(new Route('GET', '/archive/{year?}', static fn () => null));

        self::assertTrue($matcher->matchMethodAndPath('GET', '/archive')->isFound());
        self::assertSame('2026', $matcher->matchMethodAndPath('GET', '/archive/2026')->parameter('year'));
    }

    public function testItSupportsAnApplicationBasePath(): void
    {
        $routes = new RouteCollection();
        $routes->add(new Route('GET', '/users', static fn () => null));
        $matcher = new RouteMatcher($routes, '/flex-cms');

        self::assertTrue($matcher->matchMethodAndPath('GET', '/flex-cms/users')->isFound());
    }

    public function testItRecompilesWhenRoutesAreAdded(): void
    {
        $routes = new RouteCollection();
        $matcher = new RouteMatcher($routes);

        self::assertTrue($matcher->matchMethodAndPath('GET', '/users')->isNotFound());

        $routes->add(new Route('GET', '/users', static fn () => null));

        self::assertTrue($matcher->matchMethodAndPath('GET', '/users')->isFound());
    }

    private function matcherWith(Route $route): RouteMatcher
    {
        $routes = new RouteCollection();
        $routes->add($route);

        return new RouteMatcher($routes);
    }
}
