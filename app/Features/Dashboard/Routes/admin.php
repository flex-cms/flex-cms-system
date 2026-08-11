<?php

declare(strict_types=1);

use Flex\Core\Routing\FlexRouter;
use Flex\Features\Dashboard\Controllers\DashboardController;

FlexRouter::get(
    '/admin/dashboard',
    [DashboardController::class, 'index'],
)
    ->middleware(['auth', 'admin'])
    ->name('admin.dashboard');
