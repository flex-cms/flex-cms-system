<?php

declare(strict_types=1);

namespace Flex\Features\SystemHealth\Controllers;

use Flex\Core\Flex;
use Flex\Core\Http\Request;
use Flex\Core\Http\Response;

final class HealthController
{
    public function index(Request $request): Response
    {
        return Response::json([
            'status' => 'ok',
            'application' => Flex::NAME,
            'version' => Flex::VERSION,
            'php' => PHP_VERSION,
            'request' => [
                'method' => $request->method(),
                'path' => $request->path(),
            ],
            'timestamp' => date(DATE_ATOM),
        ]);
    }
}
