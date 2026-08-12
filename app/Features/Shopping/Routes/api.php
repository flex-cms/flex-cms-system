<?php

declare(strict_types=1);

use Flex\Core\Routing\FlexRouter;
use Flex\Features\Shopping\Controllers\CategoryController;

FlexRouter::get(
    '/api/admin/shopping/categories',
    [CategoryController::class, 'apiIndex']
)
    ->middleware([
        'auth',
        'admin',
    ])
    ->name(
        'api.admin.shopping.categories.index'
    );

FlexRouter::post(
    '/api/admin/shopping/categories/bulk',
    [CategoryController::class, 'bulk']
)
    ->middleware([
        'auth',
        'admin',
    ])
    ->name(
        'api.admin.shopping.categories.bulk'
    );

FlexRouter::post(
    '/api/admin/shopping/categories/{id}/toggle',
    [CategoryController::class, 'toggle']
)
    ->middleware(['auth', 'admin'])
    ->name('api.admin.shopping.categories.toggle');

FlexRouter::post(
    '/api/admin/shopping/categories/{id}/delete',
    [CategoryController::class, 'delete']
)
    ->middleware(['auth', 'admin'])
    ->name('api.admin.shopping.categories.delete');

FlexRouter::post(
    '/api/admin/shopping/categories/{id}/restore',
    [CategoryController::class, 'restore']
)
    ->middleware(['auth', 'admin'])
    ->name('api.admin.shopping.categories.restore');

FlexRouter::post(
    '/api/admin/shopping/categories/{id}/force-delete',
    [CategoryController::class, 'forceDelete']
)
    ->middleware(['auth', 'admin'])
    ->name('api.admin.shopping.categories.force-delete');
