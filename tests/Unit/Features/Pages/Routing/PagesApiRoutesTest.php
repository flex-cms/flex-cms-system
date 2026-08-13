<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Pages\Routing;

use Flex\Core\Routing\FlexRouter;
use Flex\Core\Routing\RouteCollection;
use Flex\Core\Routing\RouteMatcher;
use Flex\Core\Routing\RouteRegistrar;
use Flex\Features\Pages\Controllers\PagesController;
use Flex\Features\Pages\Controllers\PageContentController;
use Flex\Features\Pages\Controllers\PageFieldsController;
use PHPUnit\Framework\TestCase;

final class PagesApiRoutesTest extends TestCase
{
    public function testItRegistersPagesApiIndexRoute(): void
    {
        $routes = new RouteCollection();
        FlexRouter::setRegistrar(new RouteRegistrar($routes));

        try {
            require dirname(__DIR__, 5)
                . '/app/Features/Pages/Routes/api.php';

            $route = $routes->named('api.admin.pages.index');
            self::assertNotNull($route);
            self::assertSame('/api/admin/pages', $route->uri());
            self::assertSame(['GET', 'HEAD'], $route->methods());
            self::assertSame(['auth', 'admin'], $route->getMiddleware());
            self::assertSame(
                [PagesController::class, 'apiIndex'],
                $route->action()
            );
            self::assertTrue(
                (new RouteMatcher($routes))
                    ->matchMethodAndPath('GET', '/api/admin/pages')
                    ->isFound()
            );

            $bulk = $routes->named('api.admin.pages.bulk');
            self::assertNotNull($bulk);
            self::assertSame('/api/admin/pages/bulk', $bulk->uri());
            self::assertSame(['POST'], $bulk->methods());
            self::assertSame(['auth', 'admin'], $bulk->getMiddleware());
            self::assertSame(
                [PagesController::class, 'bulk'],
                $bulk->action()
            );

            foreach ([
                'toggle' => 'toggle',
                'delete' => 'delete',
                'restore' => 'restore',
                'force-delete' => 'forceDelete',
            ] as $name => $method) {
                $actionRoute = $routes->named('api.admin.pages.' . $name);
                self::assertNotNull($actionRoute);
                self::assertSame(
                    '/api/admin/pages/{id}/' . $name,
                    $actionRoute->uri()
                );
                self::assertSame(['POST'], $actionRoute->methods());
                self::assertSame(['id' => '[0-9]+'], $actionRoute->constraints());
                self::assertSame(['auth', 'admin'], $actionRoute->getMiddleware());
                self::assertSame(
                    [PagesController::class, $method],
                    $actionRoute->action()
                );
            }

            $showElements = $routes->named('api.admin.pages.elements.show');
            self::assertNotNull($showElements);
            self::assertSame(
                [PageContentController::class, 'show'],
                $showElements->action()
            );
            self::assertSame(['GET', 'HEAD'], $showElements->methods());
            self::assertSame(['id' => '[0-9]+'], $showElements->constraints());

            $updateElements = $routes->named('api.admin.pages.elements.update');
            self::assertNotNull($updateElements);
            self::assertSame(
                [PageContentController::class, 'update'],
                $updateElements->action()
            );
            self::assertSame(['PUT'], $updateElements->methods());
            self::assertSame(['auth', 'admin'], $updateElements->getMiddleware());

            $fields = $routes->named('api.admin.pages.fields.index');
            self::assertNotNull($fields);
            self::assertSame('/api/admin/pages/{pageId}/fields', $fields->uri());
            self::assertSame([PageFieldsController::class, 'apiIndex'], $fields->action());
            self::assertSame(['pageId' => '[0-9]+'], $fields->constraints());

            $deleteField = $routes->named('api.admin.pages.fields.delete');
            self::assertNotNull($deleteField);
            self::assertSame(['POST'], $deleteField->methods());
            self::assertSame([PageFieldsController::class, 'delete'], $deleteField->action());
            self::assertSame(
                ['pageId' => '[0-9]+', 'fieldId' => '[0-9]+'],
                $deleteField->constraints()
            );
        } finally {
            FlexRouter::reset();
        }
    }
}
