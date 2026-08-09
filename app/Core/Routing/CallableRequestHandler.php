<?php

declare(strict_types=1);

namespace Flex\Core\Routing;

use Closure;
use Flex\Core\Http\Contracts\RequestHandlerInterface;
use Flex\Core\Http\Request;
use Flex\Core\Http\Response;

final readonly class CallableRequestHandler implements RequestHandlerInterface
{
    public function __construct(private Closure $handler)
    {
    }

    public function handle(Request $request): Response
    {
        return ($this->handler)($request);
    }
}
