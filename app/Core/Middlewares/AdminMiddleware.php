<?php

namespace Flex\Core\Middlewares;

use Flex\Core\Auth;
use Flex\Core\Middlewares\Interfaces\MiddlewareInterface;
use Flex\Core\Routing\View;

class AdminMiddleware implements MiddlewareInterface
{
    public function handle(): void
    {
        if (!Auth::isAdmin()) {
            $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
            View::redirect('/admin');
        }
    }
}
