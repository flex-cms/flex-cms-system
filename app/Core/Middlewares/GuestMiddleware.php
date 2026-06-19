<?php

namespace Flex\Core\Middlewares;

use Flex\Core\Auth;
use Flex\Core\Interfaces\MiddlewareInterface;
use Flex\Core\Routing\View;

class GuestMiddleware implements MiddlewareInterface
{
    public function handle(): void
    {
        if (Auth::check()) {
            View::redirect('/admin');
        }
    }
}