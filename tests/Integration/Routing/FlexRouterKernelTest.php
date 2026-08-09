<?php

declare(strict_types=1);

namespace Tests\Integration\Routing;

use Flex\Core\Http\Request;
use Flex\Core\Http\Response;
use Flex\Core\Routing\FlexRouter;
use Flex\Core\Routing\FlexRouterApplication;
use PHPUnit\Framework\TestCase;

final class FlexRouterKernelTest extends TestCase
{
    protected function tearDown(): void
    {
        FlexRouter::reset();
    }

    public function testItExecutesAFoundRouteEndToEnd(): void
    {
        $app = FlexRouterApplication::create('https://flex.test');
        FlexRouter::get('/users/{id}', static fn (int $id): array => ['id' => $id])
            ->whereNumber('id')
            ->name('users.show');

        $result = $app->kernel->handle(new Request('GET', '/users/15'));

        self::assertTrue($result->isHandled());
        self::assertSame('{"id":15}', $result->response()->content());
        self::assertSame('https://flex.test/users/15', FlexRouter::route('users.show', ['id' => 15]));
    }

    public function testMissingRoutePassesDuringMigration(): void
    {
        $app = FlexRouterApplication::create(passNotFound: true);

        $result = $app->kernel->handle(new Request('GET', '/legacy-route'));

        self::assertTrue($result->shouldPass());
    }

    public function testMissingRouteReturns404AfterMigration(): void
    {
        $app = FlexRouterApplication::create(passNotFound: false);

        $result = $app->kernel->handle(new Request(
            'GET',
            '/missing',
            headers: ['accept' => 'application/json'],
        ));

        self::assertTrue($result->isHandled());
        self::assertSame(404, $result->response()->status());
    }

    public function testMethodNotAllowedContainsAllowHeader(): void
    {
        $app = FlexRouterApplication::create();
        FlexRouter::get('/users', static fn (): Response => Response::make('users'));

        $result = $app->kernel->handle(new Request('DELETE', '/users'));

        self::assertSame(405, $result->response()->status());
        self::assertSame('GET, HEAD', $result->response()->header('Allow'));
    }

    public function testHeadResponseHasNoBody(): void
    {
        $app = FlexRouterApplication::create();
        FlexRouter::get('/status', static fn (): string => 'healthy');

        $result = $app->kernel->handle(new Request('HEAD', '/status'));

        self::assertSame('', $result->response()->content());
        self::assertSame(200, $result->response()->status());
    }
}
