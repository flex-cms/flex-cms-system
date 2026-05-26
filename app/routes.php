<?php

global $router;

use Flex\Core\Controllers\AuthController;
use Flex\Core\Controllers\AdminController;
use Flex\Core\Controllers\UserController;
use Flex\Core\Controllers\RoleController;
use Flex\Core\Controllers\PermissionController;
use Flex\Core\Controllers\UpdateController;
use Flex\Core\Controllers\PluginController;
use Flex\Core\Middlewares\AuthMiddleware;
use Flex\Core\Controllers\FileStructureController;
use Flex\Core\Middlewares\AdminMiddleware;

$routes = [
    ['GET', '/admin', [AuthController::class, 'showLogin']],
    ['POST', '/admin', [AuthController::class, 'login']],
    ['GET', '/logout', [AuthController::class, 'logout']],

    // Dashboard & UI
    ['GET', '/admin/dashboard', [AdminController::class, 'index'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['POST', '/admin/sidebar-toggle', [AdminController::class, 'toggleSidebar'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['POST', '/admin/theme-toggle', [AdminController::class, 'toggleTheme'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['POST', '/admin/ui/save-state', [AdminController::class, 'saveUiState'], [AuthMiddleware::class, AdminMiddleware::class]],

    // Users (RBAC)
    ['GET', '/admin/users/index', [UserController::class, 'index'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['POST', '/admin/users/toggle', [UserController::class, 'toggle'], [AuthMiddleware::class, AdminMiddleware::class]],

    // Roles
    ['GET', '/admin/users/roles', [RoleController::class, 'index'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['GET', '/admin/users/roles/create', [RoleController::class, 'create'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['POST', '/admin/users/roles/create', [RoleController::class, 'store'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['GET', '/admin/users/roles/edit/{id}', [RoleController::class, 'edit'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['POST', '/admin/users/roles/edit/{id}', [RoleController::class, 'update'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['POST', '/admin/users/roles/toggle', [RoleController::class, 'toggle'], [AuthMiddleware::class, AdminMiddleware::class]],

    // Permissions
    ['GET', '/admin/users/permissions', [PermissionController::class, 'index'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['GET', '/admin/users/permissions/create', [PermissionController::class, 'create'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['POST', '/admin/users/permissions/create', [PermissionController::class, 'store'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['GET', '/admin/users/permissions/edit/{id}', [PermissionController::class, 'edit'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['POST', '/admin/users/permissions/update/{id}', [PermissionController::class, 'update'], [AuthMiddleware::class, AdminMiddleware::class]],

    // Updates
    ['GET', '/admin/update', [UpdateController::class, 'index'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['POST', '/admin/update', [UpdateController::class, 'update'], [AuthMiddleware::class, AdminMiddleware::class]],

    // Plugins
    ['GET', '/admin/plugins', [PluginController::class, 'index'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['POST', '/admin/plugins/toggle', [PluginController::class, 'toggle'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['POST', '/admin/plugins/delete', [PluginController::class, 'delete'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['POST', '/admin/plugins/update', [PluginController::class, 'update'], [AuthMiddleware::class, AdminMiddleware::class]],

    // File Structure
    ['GET', '/admin/file-structure', [FileStructureController::class, 'index'], [AuthMiddleware::class, AdminMiddleware::class]],
];

foreach ($routes as $route) {
    $method = strtolower($route[0]);
    $path = $route[1];
    $handler = $route[2];
    $middlewares = $route[3] ?? [];

    $router->$method($path, $handler, $middlewares);
}