<?php

declare(strict_types=1);

namespace Tests\Unit\Routing;

use Flex\Core\Routing\Exceptions\InvalidRouteActionException;
use Flex\Core\Routing\RouteParameterBinder;
use PHPUnit\Framework\TestCase;

final class RouteParameterBinderTest extends TestCase
{
    public function testItConvertsScalarRouteParameters(): void
    {
        $binder = new RouteParameterBinder();
        $action = static fn (int $id, float $price, bool $active): null => null;

        self::assertSame(
            ['id' => 12, 'price' => 15.5, 'active' => true],
            $binder->bind($action, ['id' => '12', 'price' => '15.5', 'active' => 'true']),
        );
    }

    public function testItRejectsAnInvalidInteger(): void
    {
        $binder = new RouteParameterBinder();

        $this->expectException(InvalidRouteActionException::class);
        $binder->bind(static fn (int $id): null => null, ['id' => 'invalid']);
    }
}
