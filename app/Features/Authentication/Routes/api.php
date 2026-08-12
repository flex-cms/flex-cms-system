<?php

declare(strict_types=1);

use Flex\Core\Routing\FlexRouter;
use Flex\Features\Authentication\Controllers\UserController;
use Flex\Features\Authentication\Middleware\RequirePermission;

$permission = static fn(string $slug): string =>
    RequirePermission::class . ':' . $slug;

FlexRouter::get(
    '/api/admin/authentication/users',
    [UserController::class, 'apiIndex']
)
    ->middleware([
        'auth',
        $permission('users.view'),
    ])
    ->name('api.admin.authentication.users.index');

FlexRouter::post(
    '/api/admin/authentication/users/bulk',
    [UserController::class, 'bulk']
)
    // TODO: Разделете update и delete bulk действията в отделни
    // endpoints, когато UI таблицата поддържа различен endpoint за действие.
    ->middleware([
        'auth',
        $permission('users.update'),
        $permission('users.delete'),
    ])
    ->name('api.admin.authentication.users.bulk');

FlexRouter::post(
    '/api/admin/authentication/users/{id}/toggle',
    [UserController::class, 'toggle']
)
    ->whereNumber('id')
    ->middleware([
        'auth',
        $permission('users.update'),
    ])
    ->name('api.admin.authentication.users.toggle');

FlexRouter::post(
    '/api/admin/authentication/users/{id}/delete',
    [UserController::class, 'delete']
)
    ->whereNumber('id')
    ->middleware([
        'auth',
        $permission('users.delete'),
    ])
    ->name('api.admin.authentication.users.delete');

FlexRouter::post(
    '/api/admin/authentication/users/{id}/restore',
    [UserController::class, 'restore']
)
    ->whereNumber('id')
    ->middleware([
        'auth',
        $permission('users.delete'),
    ])
    ->name('api.admin.authentication.users.restore');

FlexRouter::post(
    '/api/admin/authentication/users/{id}/force-delete',
    [UserController::class, 'forceDelete']
)
    ->whereNumber('id')
    ->middleware([
        'auth',
        $permission('users.delete'),
    ])
    ->name('api.admin.authentication.users.force-delete');
