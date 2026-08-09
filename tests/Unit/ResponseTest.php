<?php

declare(strict_types=1);

namespace Tests\Unit\Http;

use Flex\Core\Http\JsonResponse;
use Flex\Core\Http\RedirectResponse;
use Flex\Core\Http\Response;
use PHPUnit\Framework\TestCase;

final class ResponseTest extends TestCase
{
    public function testResponseMutatorsDoNotModifyTheOriginal(): void
    {
        $response = Response::make('original');
        $changed = $response->withStatus(201)->withHeader('X-Test', 'yes');

        self::assertSame(200, $response->status());
        self::assertNull($response->header('X-Test'));
        self::assertSame(201, $changed->status());
        self::assertSame('yes', $changed->header('X-Test'));
    }

    public function testItCreatesAUnicodeJsonResponse(): void
    {
        $response = Response::json(['message' => 'Успешно'], 201);

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertSame(201, $response->status());
        self::assertSame('{"message":"Успешно"}', $response->content());
        self::assertSame('application/json; charset=UTF-8', $response->header('Content-Type'));
    }

    public function testItCreatesARedirectResponse(): void
    {
        $response = Response::redirect('/login');

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame(302, $response->status());
        self::assertSame('/login', $response->header('Location'));
    }
}
