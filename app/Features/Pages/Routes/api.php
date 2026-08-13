<?php

declare(strict_types=1);

use Flex\Core\Routing\FlexRouter;
use Flex\Features\Pages\Controllers\PagesController;

FlexRouter::get(
    '/api/admin/pages',
    [PagesController::class, 'apiIndex']
)
    ->middleware(['auth', 'admin'])
    ->name('api.admin.pages.index');

FlexRouter::post(
    '/api/admin/pages/bulk',
    [PagesController::class, 'bulk']
)
    ->middleware(['auth', 'admin'])
    ->name('api.admin.pages.bulk');

FlexRouter::post(
    '/api/admin/pages/{id}/toggle',
    [PagesController::class, 'toggle']
)
    ->whereNumber('id')
    ->middleware(['auth', 'admin'])
    ->name('api.admin.pages.toggle');

FlexRouter::post(
    '/api/admin/pages/{id}/delete',
    [PagesController::class, 'delete']
)
    ->whereNumber('id')
    ->middleware(['auth', 'admin'])
    ->name('api.admin.pages.delete');

FlexRouter::post(
    '/api/admin/pages/{id}/restore',
    [PagesController::class, 'restore']
)
    ->whereNumber('id')
    ->middleware(['auth', 'admin'])
    ->name('api.admin.pages.restore');

FlexRouter::post(
    '/api/admin/pages/{id}/force-delete',
    [PagesController::class, 'forceDelete']
)
    ->whereNumber('id')
    ->middleware(['auth', 'admin'])
    ->name('api.admin.pages.force-delete');
