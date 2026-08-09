<?php

declare(strict_types=1);

namespace Tests\Unit\Routing;

use Flex\Core\Container\Container;
use Flex\Core\Http\Contracts\MiddlewareInterface;
use Flex\Core\Http\Contracts\RequestHandlerInterface;
use Flex\Core\Http\Request;
use Flex\Core\Http\Response;
use Flex\Core\Routing\MiddlewarePipeline;
use Flex\Core\Routing\MiddlewareRegistry;
use PHPUnit\Framework\TestCase;

final class MiddlewarePipelineTest extends TestCase
{
    public function testMiddlewareRunsInRegistrationOrder(): void
    {
        TraceMiddleware::$trace = [];
        $registry = (new MiddlewareRegistry())->alias('trace', TraceMiddleware::class);
        $pipeline = new MiddlewarePipeline(new Container(), $registry);

        $response = $pipeline->process(
            new Request('GET', '/'),
            ['trace:first', 'trace:second'],
            static function (): Response {
                TraceMiddleware::$trace[] = 'destination';
                return Response::make('ok');
            },
        );

        self::assertSame('ok', $response->content());
        self::assertSame(
            ['before:first', 'before:second', 'destination', 'after:second', 'after:first'],
            TraceMiddleware::$trace,
        );
    }

    public function testMiddlewareMayStopThePipelineEarly(): void
    {
        $registry = (new MiddlewareRegistry())->alias('blocked', BlockingMiddleware::class);
        $pipeline = new MiddlewarePipeline(new Container(), $registry);
        $destinationWasCalled = false;

        $response = $pipeline->process(
            new Request('GET', '/admin'),
            ['blocked'],
            static function () use (&$destinationWasCalled): Response {
                $destinationWasCalled = true;
                return Response::make('private');
            },
        );

        self::assertFalse($destinationWasCalled);
        self::assertSame(403, $response->status());
    }

    public function testGlobalMiddlewareCanBePrependedAndAppended(): void
    {
        $registry = (new MiddlewareRegistry())
            ->appendGlobal('second')
            ->prependGlobal('first');

        self::assertSame(['first', 'second'], $registry->global());
    }
}

final class TraceMiddleware implements MiddlewareInterface
{
    /** @var list<string> */
    public static array $trace = [];

    public function process(
        Request $request,
        RequestHandlerInterface $next,
        string ...$parameters,
    ): Response {
        $label = $parameters[0] ?? 'unknown';
        self::$trace[] = "before:{$label}";
        $response = $next->handle($request);
        self::$trace[] = "after:{$label}";

        return $response;
    }
}

final class BlockingMiddleware implements MiddlewareInterface
{
    public function process(
        Request $request,
        RequestHandlerInterface $next,
        string ...$parameters,
    ): Response {
        return Response::make('Forbidden', 403);
    }
}
