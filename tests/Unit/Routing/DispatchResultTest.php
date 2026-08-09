<?php

declare(strict_types=1);

namespace Tests\Unit\Routing;

use Flex\Core\Routing\DispatchResult;
use Flex\Core\Routing\Route;
use LogicException;
use PHPUnit\Framework\TestCase;

final class DispatchResultTest extends TestCase
{
    public function testFoundResultContainsTheRouteAndParameters(): void
    {
        $route = new Route('GET', '/users/{id}', static fn () => null);
        $result = DispatchResult::found($route, ['id' => '15']);

        self::assertTrue($result->isFound());
        self::assertSame($route, $result->route());
        self::assertSame('15', $result->parameter('id'));
    }

    public function testNotFoundResultDoesNotExposeARoute(): void
    {
        $result = DispatchResult::notFound();

        $this->expectException(LogicException::class);
        $result->route();
    }

    public function testAllowedMethodsAreNormalized(): void
    {
        $result = DispatchResult::methodNotAllowed(['post', 'GET', 'POST']);

        self::assertSame(['GET', 'POST'], $result->allowedMethods());
    }
}
