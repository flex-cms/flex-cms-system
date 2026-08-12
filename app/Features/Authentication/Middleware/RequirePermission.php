<?php

declare(strict_types=1);

namespace Flex\Features\Authentication\Middleware;

use Flex\Core\Http\Contracts\MiddlewareInterface;
use Flex\Core\Http\Contracts\RequestHandlerInterface;
use Flex\Core\Http\Request;
use Flex\Core\Http\Response;
use Flex\Features\Authentication\Services\AuthorizationService;

final readonly class RequirePermission implements MiddlewareInterface
{
    public function __construct(
        private AuthorizationService $authorization
    ) {
    }

    public function process(
        Request $request,
        RequestHandlerInterface $next,
        string ...$parameters
    ): Response {
        $permission = $parameters[0] ?? '';

        if (
            $permission !== ''
            && $this->authorization->can($permission)
        ) {
            return $next->handle($request);
        }

        return $request->expectsJson()
            ? Response::json([
                'status' => 'error',
                'message' => 'Forbidden.',
            ], 403)
            : Response::html(
                '<h1>403 - Forbidden</h1>',
                403
            );
    }
}
