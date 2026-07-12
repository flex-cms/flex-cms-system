<?php

namespace Flex\Core\Middlewares;

use Flex\Core\Auth;
use Flex\Core\Middlewares\Interfaces\MiddlewareInterface;
use Flex\Core\Routing\View;
use Flex\Models\Plugin;

class AuthMiddleware implements MiddlewareInterface
{
    public function handle(): void
    {
        if (!Auth::check()) {
            $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];

            if (Plugin::isActive('BasicAuthentication')) {
                View::redirect('/auth/login');
            }

            View::redirect('/login');
        }
    }
}