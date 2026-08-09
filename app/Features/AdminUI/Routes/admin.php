<?php

declare(strict_types=1);

use Flex\Core\Routing\FlexRouter;
use Flex\Features\AdminUI\Controllers\PreviewController;

FlexRouter::prefix('/admin')
    ->name('admin.ui.')
    ->middleware(['auth', 'admin'])
    ->group(static function (): void {
        FlexRouter::get(
            '/ui-preview',
            [PreviewController::class, 'show']
        )->name('preview');
    });
