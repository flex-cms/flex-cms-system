<?php

declare(strict_types=1);

use Flex\Core\Routing\FlexRouter;
use Flex\Features\Pages\Controllers\PagesController;
use Flex\Features\Pages\Controllers\PageContentController;
use Flex\Features\Pages\Controllers\PageFieldsController;

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
        FlexRouter::get('/{id}/content', [PageContentController::class, 'edit'])
            ->whereNumber('id')
            ->name('content.edit');
        FlexRouter::get('/{pageId}/fields', [PageFieldsController::class, 'index'])
            ->whereNumber('pageId')->name('fields.index');
        FlexRouter::get('/{pageId}/fields/create', [PageFieldsController::class, 'create'])
            ->whereNumber('pageId')->name('fields.create');
        FlexRouter::post('/{pageId}/fields/store', [PageFieldsController::class, 'store'])
            ->whereNumber('pageId')->name('fields.store');
        FlexRouter::get('/{pageId}/fields/import', [PageFieldsController::class, 'importForm'])
            ->whereNumber('pageId')->name('fields.import-form');
        FlexRouter::post('/{pageId}/fields/import', [PageFieldsController::class, 'import'])
            ->whereNumber('pageId')->name('fields.import');
        FlexRouter::get('/{pageId}/fields/{fieldId}/edit', [PageFieldsController::class, 'edit'])
            ->whereNumber('pageId')->whereNumber('fieldId')->name('fields.edit');
        FlexRouter::post('/{pageId}/fields/{fieldId}/update', [PageFieldsController::class, 'update'])
            ->whereNumber('pageId')->whereNumber('fieldId')->name('fields.update');
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
