<?php

declare(strict_types=1);

namespace Tests\Unit\Routing;

use Flex\Core\Container\Container;
use Flex\Core\Http\JsonResponse;
use Flex\Core\Http\Request;
use Flex\Core\Http\Response;
use Flex\Core\Routing\ControllerDispatcher;
use Flex\Core\Routing\DispatchResult;
use Flex\Core\Routing\Exceptions\InvalidRouteResponseException;
use Flex\Core\Routing\Route;
use PHPUnit\Framework\TestCase;

final class ControllerDispatcherTest extends TestCase
{
    public function testItDispatchesAClosureAndBindsAnIntegerParameter(): void
    {
        $dispatcher = $this->dispatcher();
        $route = new Route('GET', '/users/{id}', static fn (int $id): string => "user:{$id}");

        $response = $dispatcher->dispatch(
            DispatchResult::found($route, ['id' => '15']),
            new Request('GET', '/users/15'),
        );

        self::assertSame('user:15', $response->content());
        self::assertSame('text/html; charset=UTF-8', $response->header('Content-Type'));
    }

    public function testItInjectsTheBoundRequestAndControllerDependency(): void
    {
        $dispatcher = $this->dispatcher();
        $route = new Route('GET', '/users/{id}', [TestController::class, 'show']);

        $response = $dispatcher->dispatch(
            DispatchResult::found($route, ['id' => '21']),
            new Request('GET', '/users/21'),
        );

        self::assertSame('21:21:service', $response->content());
    }

    public function testItNormalizesArraysToJson(): void
    {
        $dispatcher = $this->dispatcher();
        $route = new Route('GET', '/api/status', static fn (): array => ['status' => 'ok']);

        $response = $dispatcher->dispatch(
            DispatchResult::found($route),
            new Request('GET', '/api/status'),
        );

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertSame('{"status":"ok"}', $response->content());
    }

    public function testItPreservesAResponseReturnedByTheAction(): void
    {
        $dispatcher = $this->dispatcher();
        $expected = Response::json(['created' => true], 201);
        $route = new Route('POST', '/users', static fn (): Response => $expected);

        self::assertSame(
            $expected,
            $dispatcher->dispatch(DispatchResult::found($route), new Request('POST', '/users')),
        );
    }

    public function testItCapturesLegacyOutputWhenTheActionReturnsNull(): void
    {
        $dispatcher = $this->dispatcher();
        $route = new Route('GET', '/legacy', static function (): void {
            echo 'legacy output';
        });

        $response = $dispatcher->dispatch(
            DispatchResult::found($route),
            new Request('GET', '/legacy'),
        );

        self::assertSame('legacy output', $response->content());
    }

    public function testItRejectsMixingOutputAndAReturnValue(): void
    {
        $dispatcher = $this->dispatcher();
        $route = new Route('GET', '/invalid', static function (): string {
            echo 'output';
            return 'returned';
        });

        $this->expectException(InvalidRouteResponseException::class);
        $dispatcher->dispatch(DispatchResult::found($route), new Request('GET', '/invalid'));
    }

    private function dispatcher(): ControllerDispatcher
    {
        return new ControllerDispatcher(new Container());
    }
}

final class TestService
{
    public function value(): string
    {
        return 'service';
    }
}

final class TestController
{
    public function __construct(private readonly TestService $service)
    {
    }

    public function show(Request $request, int $id): string
    {
        return $request->route('id') . ':' . $id . ':' . $this->service->value();
    }
}
