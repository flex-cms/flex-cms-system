<?php

namespace Flex\Core\Middlewares;

use Flex\Core\Auth;
use Flex\Core\Interfaces\MiddlewareInterface;
use Flex\Core\Routing\View;

class AuthMiddleware implements MiddlewareInterface
{
    public function handle(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!Auth::check()) {
            $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
            View::redirect('/auth/login', 301);
        }
    }
}