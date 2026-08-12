<?php

declare(strict_types=1);

use Flex\Core\Routing\FlexRouter;
use Flex\Features\Shopping\Controllers\CategoryController;
use Flex\Features\Shopping\Controllers\ProductController;

FlexRouter::prefix('/admin/shopping/categories')
    ->name('admin.shopping.categories.')
    ->middleware(['auth', 'admin'])
    ->group(static function (): void {
        FlexRouter::get(
            '',
            [CategoryController::class, 'index']
        )->name('index');

        FlexRouter::get(
            '/create',
            [CategoryController::class, 'create']
        )->name('create');

        FlexRouter::post(
            '/store',
            [CategoryController::class, 'store']
        )->name('store');

        FlexRouter::get(
            '/{id}/edit',
            [CategoryController::class, 'edit']
        )->name('edit');

        FlexRouter::post(
            '/{id}/update',
            [CategoryController::class, 'update']
        )->name('update');

        FlexRouter::post(
            '/{id}/toggle',
            [CategoryController::class, 'toggle']
        )->name('toggle');

        FlexRouter::post(
            '/{id}/delete',
            [CategoryController::class, 'delete']
        )->name('delete');

        FlexRouter::post(
            '/{id}/restore',
            [CategoryController::class, 'restore']
        )->name('restore');

        FlexRouter::post(
            '/{id}/force-delete',
            [CategoryController::class, 'forceDelete']
        )->name('force-delete');
    });

FlexRouter::prefix('/admin/shopping/products')
    ->name('admin.shopping.products.')
    ->middleware(['auth', 'admin'])
    ->group(static function (): void {
        FlexRouter::get('', [ProductController::class, 'index'])->name('index');
        FlexRouter::get('/create', [ProductController::class, 'create'])->name('create');
        FlexRouter::post('/store', [ProductController::class, 'store'])->name('store');
        FlexRouter::get('/{id}/edit', [ProductController::class, 'edit'])->name('edit');
        FlexRouter::post('/{id}/update', [ProductController::class, 'update'])->name('update');
        FlexRouter::post('/{id}/toggle', [ProductController::class, 'toggle'])->name('toggle');
        FlexRouter::post('/{id}/delete', [ProductController::class, 'delete'])->name('delete');
        FlexRouter::post('/{id}/restore', [ProductController::class, 'restore'])->name('restore');
        FlexRouter::post('/{id}/force-delete', [ProductController::class, 'forceDelete'])->name('force-delete');
    });
