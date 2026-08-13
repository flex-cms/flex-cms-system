<?php

declare(strict_types=1);

use Flex\Core\Routing\FlexRouter;
use Flex\Features\Pages\Controllers\PagesController;

FlexRouter::prefix('/admin/pages')
    ->name('admin.pages.')
    ->middleware(['auth', 'admin'])
    ->group(static function (): void {
        FlexRouter::get('/', [PagesController::class, 'index'])
            ->name('index');
        FlexRouter::get('/create', [PagesController::class, 'create'])
            ->name('create');
        FlexRouter::post('/store', [PagesController::class, 'store'])
            ->name('store');
        FlexRouter::get('/edit/{id}', [PagesController::class, 'edit'])
            ->whereNumber('id')
            ->name('edit');
        FlexRouter::post('/update/{id}', [PagesController::class, 'update'])
            ->whereNumber('id')
            ->name('update');
        FlexRouter::post('/{id}/toggle', [PagesController::class, 'toggle'])
            ->whereNumber('id')
            ->name('toggle');
        FlexRouter::post('/{id}/delete', [PagesController::class, 'delete'])
            ->whereNumber('id')
            ->name('delete');
        FlexRouter::post('/{id}/restore', [PagesController::class, 'restore'])
            ->whereNumber('id')
            ->name('restore');
        FlexRouter::post('/{id}/force-delete', [PagesController::class, 'forceDelete'])
            ->whereNumber('id')
            ->name('force-delete');
        FlexRouter::post('/reorder', [PagesController::class, 'reorder'])
            ->name('reorder');
    });
