<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Settings\Routing;

use Flex\Core\Routing\FlexRouter;
use Flex\Core\Routing\Route;
use Flex\Core\Routing\RouteCollection;
use Flex\Core\Routing\RouteMatcher;
use Flex\Core\Routing\RouteRegistrar;
use Flex\Features\Settings\Controllers\SettingsController;
use PHPUnit\Framework\TestCase;

final class SettingsRoutesTest extends TestCase
{
    private RouteCollection $routes;

    protected function setUp(): void
    {
        parent::setUp();

        $this->routes = new RouteCollection();

        FlexRouter::setRegistrar(
            new RouteRegistrar($this->routes)
        );

        require dirname(__DIR__, 5)
            . '/app/Features/Settings/Routes/admin.php';
    }

    protected function tearDown(): void
    {
        FlexRouter::reset();

        parent::tearDown();
    }

    public function testItRegistersSettingsRoutes(): void
    {
        self::assertCount(3, $this->routes);

        self::assertTrue(
            $this->routes->hasNamed(
                'admin.settings.runtime.date'
            )
        );

        self::assertTrue(
            $this->routes->hasNamed(
                'admin.settings.show'
            )
        );

        self::assertTrue(
            $this->routes->hasNamed(
                'admin.settings.update'
            )
        );
    }

    public function testItRegistersShowRoute(): void
    {
        $route = $this->route(
            'admin.settings.show'
        );

        self::assertSame(
            '/admin/settings/{group}',
            $route->uri()
        );

        self::assertContains('GET', $route->methods());
        self::assertContains('HEAD', $route->methods());

        self::assertSame(
            [
                SettingsController::class,
                'show',
            ],
            $route->action()
        );

        self::assertSame(
            ['auth', 'admin'],
            $route->getMiddleware()
        );

        self::assertSame(
            [
                'group' => 'general|mail|media',
            ],
            $route->constraints()
        );
    }

    public function testItRegistersUpdateRoute(): void
    {
        $route = $this->route(
            'admin.settings.update'
        );

        self::assertSame(
            '/admin/settings/{group}/update',
            $route->uri()
        );

        self::assertSame(
            ['POST'],
            $route->methods()
        );

        self::assertSame(
            [
                SettingsController::class,
                'update',
            ],
            $route->action()
        );

        self::assertSame(
            ['auth', 'admin'],
            $route->getMiddleware()
        );

        self::assertSame(
            [
                'group' => 'general|mail|media',
            ],
            $route->constraints()
        );
    }

    public function testItMatchesAllowedSettingsGroups(): void
    {
        $matcher = new RouteMatcher($this->routes);

        foreach (['general', 'mail', 'media'] as $group) {
            $result = $matcher->matchMethodAndPath(
                'GET',
                '/admin/settings/' . $group
            );

            self::assertTrue(
                $result->isFound(),
                sprintf(
                    'Settings group [%s] was not matched.',
                    $group
                )
            );

            self::assertSame(
                'admin.settings.show',
                $result->route()->getName()
            );

            self::assertSame(
                $group,
                $result->parameter('group')
            );
        }
    }

    public function testItDoesNotMatchUnknownSettingsGroup(): void
    {
        $matcher = new RouteMatcher($this->routes);

        $result = $matcher->matchMethodAndPath(
            'GET',
            '/admin/settings/unknown'
        );

        self::assertTrue($result->isNotFound());
    }

    public function testItMatchesSettingsUpdateRoute(): void
    {
        $matcher = new RouteMatcher($this->routes);

        $result = $matcher->matchMethodAndPath(
            'POST',
            '/admin/settings/general/update'
        );

        self::assertTrue($result->isFound());

        self::assertSame(
            'admin.settings.update',
            $result->route()->getName()
        );

        self::assertSame(
            'general',
            $result->parameter('group')
        );
    }

    public function testUpdateRouteRejectsGetRequest(): void
    {
        $matcher = new RouteMatcher($this->routes);

        $result = $matcher->matchMethodAndPath(
            'GET',
            '/admin/settings/general/update'
        );

        self::assertTrue(
            $result->isMethodNotAllowed()
        );

        self::assertSame(
            ['POST'],
            $result->allowedMethods()
        );
    }

    private function route(string $name): Route
    {
        $route = $this->routes->named($name);

        self::assertNotNull(
            $route,
            sprintf(
                'Route [%s] was not registered.',
                $name
            )
        );

        return $route;
    }
}
