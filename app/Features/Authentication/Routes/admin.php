<?php

declare(strict_types=1);

use Flex\Core\Routing\FlexRouter;
use Flex\Features\Authentication\Controllers\PermissionController;
use Flex\Features\Authentication\Controllers\RoleController;
use Flex\Features\Authentication\Controllers\UserController;
use Flex\Features\Authentication\Middleware\RequirePermission;

$permission = static fn(string $slug): string =>
    RequirePermission::class . ':' . $slug;

FlexRouter::prefix('/admin/authentication/users')
    ->name('admin.authentication.users.')
    ->middleware('auth')
    ->group(static function () use ($permission): void {
        FlexRouter::get(
            '',
            [UserController::class, 'index']
        )
            ->middleware($permission('users.view'))
            ->name('index');

        FlexRouter::get(
            '/create',
            [UserController::class, 'create']
        )
            ->middleware($permission('users.create'))
            ->name('create');

        FlexRouter::post(
            '/store',
            [UserController::class, 'store']
        )
            ->middleware($permission('users.create'))
            ->name('store');

        FlexRouter::get(
            '/{id}/edit',
            [UserController::class, 'edit']
        )
            ->whereNumber('id')
            ->middleware($permission('users.update'))
            ->name('edit');

        FlexRouter::post(
            '/{id}/update',
            [UserController::class, 'update']
        )
            ->whereNumber('id')
            ->middleware($permission('users.update'))
            ->name('update');

        FlexRouter::post(
            '/{id}/toggle',
            [UserController::class, 'toggle']
        )
            ->whereNumber('id')
            ->middleware($permission('users.update'))
            ->name('toggle');

        FlexRouter::post(
            '/{id}/delete',
            [UserController::class, 'delete']
        )
            ->whereNumber('id')
            ->middleware($permission('users.delete'))
            ->name('delete');

        FlexRouter::post(
            '/{id}/restore',
            [UserController::class, 'restore']
        )
            ->whereNumber('id')
            ->middleware($permission('users.delete'))
            ->name('restore');

        FlexRouter::post(
            '/{id}/force-delete',
            [UserController::class, 'forceDelete']
        )
            ->whereNumber('id')
            ->middleware($permission('users.delete'))
            ->name('force-delete');
    });

FlexRouter::prefix('/admin/authentication')
    ->name('admin.authentication.')
    ->middleware('auth')
    ->group(static function () use ($permission): void {
        FlexRouter::get(
            '/roles',
            [RoleController::class, 'index']
        )
            ->middleware($permission('roles.view'))
            ->name('roles.index');

        FlexRouter::get(
            '/roles/create',
            [RoleController::class, 'create']
        )
            ->middleware($permission('roles.create'))
            ->name('roles.create');

        FlexRouter::post(
            '/roles',
            [RoleController::class, 'store']
        )
            ->middleware($permission('roles.create'))
            ->name('roles.store');

        FlexRouter::get(
            '/roles/{id}/edit',
            [RoleController::class, 'edit']
        )
            ->whereNumber('id')
            ->middleware($permission('roles.update'))
            ->name('roles.edit');

        FlexRouter::post(
            '/roles/{id}',
            [RoleController::class, 'update']
        )
            ->whereNumber('id')
            ->middleware($permission('roles.update'))
            ->name('roles.update');

        FlexRouter::post(
            '/roles/{id}/delete',
            [RoleController::class, 'delete']
        )
            ->whereNumber('id')
            ->middleware($permission('roles.delete'))
            ->name('roles.delete');

        FlexRouter::get(
            '/permissions',
            [PermissionController::class, 'index']
        )
            ->middleware($permission('permissions.view'))
            ->name('permissions.index');
    });
