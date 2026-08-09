<?php

declare(strict_types=1);

namespace Flex\Core\Http\Contracts;

use Flex\Core\Http\Request;
use Flex\Core\Http\Response;

interface MiddlewareInterface
{
    public function process(
        Request $request,
        RequestHandlerInterface $next,
        string ...$parameters,
    ): Response;
}
