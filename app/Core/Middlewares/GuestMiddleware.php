<?php

namespace Flex\Core\Middlewares;

use Flex\Core\Auth;
use Flex\Core\Interfaces\MiddlewareInterface;
use Flex\Core\Routing\View;

class GuestMiddleware implements MiddlewareInterface
{
    public function handle(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (Auth::check()) {
            View::redirect('/admin', 302);
        }
    }
}
