<?php

declare(strict_types=1);

namespace Tests\Unit\Http;

use Flex\Core\Http\Request;
use PHPUnit\Framework\TestCase;

final class RequestTest extends TestCase
{
    public function testItNormalizesThePathAndReadsInput(): void
    {
        $request = new Request(
            method: 'post',
            uri: '/api//users/15?tab=profile',
            query: ['page' => '2'],
            body: ['user' => ['name' => 'Кристиан']],
        );

        self::assertSame('POST', $request->method());
        self::assertSame('/api/users/15', $request->path());
        self::assertSame(2, $request->integer('page'));
        self::assertSame('Кристиан', $request->input('user.name'));
        self::assertTrue($request->expectsJson());
    }

    public function testRouteAttributesAreImmutable(): void
    {
        $request = new Request('GET', '/users/42');
        $bound = $request->withAttribute('_route_parameters', ['id' => 42]);

        self::assertNull($request->route('id'));
        self::assertSame(42, $bound->route('id'));
    }

    public function testItReadsJsonInput(): void
    {
        $request = new Request(
            method: 'POST',
            uri: '/api/users',
            headers: ['content-type' => 'application/json'],
            rawBody: '{"active":true,"age":27}',
        );

        self::assertTrue($request->boolean('active'));
        self::assertSame(27, $request->integer('age'));
    }
}
