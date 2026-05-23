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

$routes = [
    // Auth маршрути
    ['GET', '/admin', [AuthController::class, 'showLogin']],
    ['POST', '/admin', [AuthController::class, 'login']],
    ['GET', '/logout', [AuthController::class, 'logout']],

    // Dashboard & UI
    ['GET', '/admin/dashboard', [AdminController::class, 'index'], [AuthMiddleware::class]],
    ['POST', '/admin/sidebar-toggle', [AdminController::class, 'toggleSidebar'], [AuthMiddleware::class]],
    ['POST', '/admin/theme-toggle', [AdminController::class, 'toggleTheme'], [AuthMiddleware::class]],
    ['POST', '/admin/ui/save-state', [AdminController::class, 'saveUiState'], [AuthMiddleware::class]],

    // Users (RBAC)
    ['GET', '/admin/users', [UserController::class, 'index'], [AuthMiddleware::class]],
    ['POST', '/admin/users/toggle', [UserController::class, 'toggle'], [AuthMiddleware::class]],

    // Roles
    ['GET', '/admin/roles', [RoleController::class, 'index'], [AuthMiddleware::class]],
    ['GET', '/admin/roles/create', [RoleController::class, 'create'], [AuthMiddleware::class]],
    ['POST', '/admin/roles/create', [RoleController::class, 'store'], [AuthMiddleware::class]],
    ['GET', '/admin/roles/edit/{id}', [RoleController::class, 'edit'], [AuthMiddleware::class]],
    ['POST', '/admin/roles/edit/{id}', [RoleController::class, 'update'], [AuthMiddleware::class]],
    ['POST', '/admin/roles/toggle', [RoleController::class, 'toggle'], [AuthMiddleware::class]],

    // Permissions
    ['GET', '/admin/permissions', [PermissionController::class, 'index'], [AuthMiddleware::class]],
    ['GET', '/admin/permissions/create', [PermissionController::class, 'create'], [AuthMiddleware::class]],
    ['POST', '/admin/permissions/create', [PermissionController::class, 'store'], [AuthMiddleware::class]],
    ['GET', '/admin/permissions/edit/{id}', [PermissionController::class, 'edit'], [AuthMiddleware::class]],
    ['POST', '/admin/permissions/edit/{id}', [PermissionController::class, 'update'], [AuthMiddleware::class]],

    // Updates
    ['GET', '/admin/update', [UpdateController::class, 'index'], [AuthMiddleware::class]],
    ['POST', '/admin/update', [UpdateController::class, 'update'], [AuthMiddleware::class]],

    // Plugins
    ['GET', '/admin/plugins', [PluginController::class, 'index'], [AuthMiddleware::class]],
    ['POST', '/admin/plugins/toggle', [PluginController::class, 'toggle'], [AuthMiddleware::class]],
    ['POST', '/admin/plugins/delete', [PluginController::class, 'delete'], [AuthMiddleware::class]],
    
    // File Structure
    ['GET', '/admin/file-structure', [FileStructureController::class, 'index'], [AuthMiddleware::class]],
];

foreach ($routes as $route) {
    $method = strtolower($route[0]);
    $path = $route[1];
    $handler = $route[2];
    $middlewares = $route[3] ?? [];

    $router->$method($path, $handler, $middlewares);
}