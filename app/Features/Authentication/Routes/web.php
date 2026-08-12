<?php

declare(strict_types=1);

use Flex\Core\Routing\FlexRouter;
use Flex\Features\Authentication\Controllers\LoginController;

FlexRouter::get('/login', [LoginController::class, 'show'])
    ->name('authentication.login');

FlexRouter::post('/login', [LoginController::class, 'login'])
    ->name('authentication.login.submit');

FlexRouter::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('authentication.logout');
