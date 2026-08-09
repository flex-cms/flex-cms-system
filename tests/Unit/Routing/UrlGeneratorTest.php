<?php

declare(strict_types=1);

namespace Tests\Unit\Routing;

use Flex\Core\Routing\Exceptions\RouteGenerationException;
use Flex\Core\Routing\Route;
use Flex\Core\Routing\RouteCollection;
use Flex\Core\Routing\UrlGenerator;
use PHPUnit\Framework\TestCase;

final class UrlGeneratorTest extends TestCase
{
    public function testItGeneratesAbsoluteAndRelativeUrls(): void
    {
        $generator = $this->generator(
            (new Route('GET', '/users/{id}', static fn () => null))
                ->whereNumber('id')
                ->name('users.show'),
        );

        self::assertSame('https://flex.test/users/15', $generator->route('users.show', ['id' => 15]));
        self::assertSame('/users/15', $generator->route('users.show', ['id' => 15], false));
    }

    public function testUnusedParametersBecomeAQueryString(): void
    {
        $generator = $this->generator(
            (new Route('GET', '/users', static fn () => null))->name('users.index'),
        );

        self::assertSame(
            '/users?page=2&status=active',
            $generator->route('users.index', ['page' => 2, 'status' => 'active'], false),
        );
    }

    public function testAnAbsentOptionalParameterIsRemoved(): void
    {
        $generator = $this->generator(
            (new Route('GET', '/archive/{year}/{month?}', static fn () => null))->name('archive'),
        );

        self::assertSame('/archive/2026', $generator->route('archive', ['year' => 2026], false));
    }

    public function testItRejectsMissingRequiredParameters(): void
    {
        $generator = $this->generator(
            (new Route('GET', '/users/{id}', static fn () => null))->name('users.show'),
        );

        $this->expectException(RouteGenerationException::class);
        $generator->route('users.show');
    }

    public function testItValidatesRouteConstraints(): void
    {
        $generator = $this->generator(
            (new Route('GET', '/users/{id}', static fn () => null))
                ->whereNumber('id')
                ->name('users.show'),
        );

        $this->expectException(RouteGenerationException::class);
        $generator->route('users.show', ['id' => 'invalid']);
    }

    private function generator(Route $route): UrlGenerator
    {
        $routes = new RouteCollection();
        $routes->add($route);

        return new UrlGenerator($routes, 'https://flex.test');
    }
}
