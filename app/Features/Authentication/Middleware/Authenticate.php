<?php

declare(strict_types=1);

namespace Flex\Features\Authentication\Middleware;

use Flex\Core\Http\Contracts\MiddlewareInterface;
use Flex\Core\Http\Contracts\RequestHandlerInterface;
use Flex\Core\Http\Request;
use Flex\Core\Http\Response;
use Flex\Features\Authentication\Contracts\AuthenticatorInterface;
use Flex\Features\Authentication\Contracts\LoginUrlResolverInterface;

final readonly class Authenticate implements MiddlewareInterface
{
    public function __construct(
        private AuthenticatorInterface $auth,
        private LoginUrlResolverInterface $loginUrls,
    ) {
    }

    public function process(Request $request, RequestHandlerInterface $next, string ...$parameters): Response
    {
        if ($this->auth->check()) {
            return $next->handle($request);
        }

        if ($request->expectsJson()) {
            return Response::json([
                'status' => 'error',
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $_SESSION['redirect_url'] = $request->uri();

        return Response::redirect($this->loginUrls->loginUrl());
    }
}
