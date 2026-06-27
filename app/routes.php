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
use Flex\Core\Controllers\SettingsController;
use Flex\Core\Controllers\PageController;
use Flex\Core\Controllers\ThemeController;
use Flex\Core\Controllers\EmailTemplateController;
use Flex\Core\Controllers\InstallController;

$routes = [
    ['GET', '/admin', [AuthController::class, 'showLogin']],
    ['POST', '/admin', [AuthController::class, 'login']],
    ['GET', '/logout', [AuthController::class, 'logout']],

    // Dashboard & UI
    ['GET', '/admin/dashboard', [AdminController::class, 'index'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['POST', '/admin/sidebar-toggle', [AdminController::class, 'toggleSidebar'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['POST', '/admin/theme-toggle', [AdminController::class, 'toggleTheme'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['POST', '/admin/ui/save-state', [AdminController::class, 'saveUiState'], [AuthMiddleware::class, AdminMiddleware::class]],

    // Users
    ['GET', '/admin/users/index', [UserController::class, 'index'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['GET', '/admin/users/create', [UserController::class, 'create'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['POST', '/admin/users/store', [UserController::class, 'store'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['GET', '/admin/users/edit/{id}', [UserController::class, 'edit'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['POST', '/admin/users/update/{id}', [UserController::class, 'update'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['POST', '/admin/users/delete/{id}', [UserController::class, 'delete'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['POST', '/admin/users/toggle', [UserController::class, 'toggle'], [AuthMiddleware::class, AdminMiddleware::class]],

    // Roles
    ['GET', '/admin/users/roles', [RoleController::class, 'index'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['GET', '/admin/users/roles/create', [RoleController::class, 'create'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['POST', '/admin/users/roles/create', [RoleController::class, 'store'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['GET', '/admin/users/roles/edit/{id}', [RoleController::class, 'edit'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['POST', '/admin/users/roles/edit/{id}', [RoleController::class, 'update'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['POST', '/admin/users/roles/toggle', [RoleController::class, 'toggle'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['POST', '/admin/users/roles/delete', [RoleController::class, 'delete'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['POST', '/admin/users/roles/force-delete', [RoleController::class, 'forceDelete'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['POST', '/admin/users/roles/restore', [RoleController::class, 'restore'], [AuthMiddleware::class, AdminMiddleware::class]],

    // Permissions
    ['GET', '/admin/users/permissions', [PermissionController::class, 'index'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['GET', '/admin/users/permissions/create', [PermissionController::class, 'create'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['POST', '/admin/users/permissions/store', [PermissionController::class, 'store'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['GET', '/admin/users/permissions/edit/{id}', [PermissionController::class, 'edit'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['POST', '/admin/users/permissions/update/{id}', [PermissionController::class, 'update'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['POST', '/admin/users/permissions/delete', [PermissionController::class, 'delete'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['POST', '/admin/users/permissions/force-delete', [PermissionController::class, 'forceDelete'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['POST', '/admin/users/permissions/restore', [PermissionController::class, 'restore'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['POST', '/admin/users/permissions/toggle', [PermissionController::class, 'toggle'], [AuthMiddleware::class, AdminMiddleware::class]],

    // Settings
    ['GET', '/admin/settings/{group}', [SettingsController::class, 'show'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['POST', '/admin/settings/{group}/update', [SettingsController::class, 'update'], [AuthMiddleware::class, AdminMiddleware::class]],

    // Updates
    ['GET', '/admin/update', [UpdateController::class, 'index'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['POST', '/admin/update/process', [UpdateController::class, 'process'], [AuthMiddleware::class, AdminMiddleware::class]],

    // Plugins
    ['GET', '/admin/plugins', [PluginController::class, 'index'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['POST', '/admin/plugins/toggle', [PluginController::class, 'toggle'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['POST', '/admin/plugins/delete', [PluginController::class, 'delete'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['POST', '/admin/plugins/update', [PluginController::class, 'update'], [AuthMiddleware::class, AdminMiddleware::class]],

    // Themes
    ['GET', '/admin/themes/all', [ThemeController::class, 'index'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['POST', '/admin/themes/activate', [ThemeController::class, 'activate'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['POST', '/admin/themes/deactivate', [ThemeController::class, 'deactivate'], [AuthMiddleware::class, AdminMiddleware::class]],

    // Pages
    ['GET', '/admin/pages', [PageController::class, 'index'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['GET', '/admin/pages/create', [PageController::class, 'create'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['POST', '/admin/pages/store', [PageController::class, 'store'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['GET', '/admin/pages/edit/{id}', [PageController::class, 'edit'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['POST', '/admin/pages/delete', [PageController::class, 'delete'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['POST', '/admin/pages/update/{id}', [PageController::class, 'update'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['POST', '/admin/pages/toggle', [PageController::class, 'toggle'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['POST', '/admin/pages/restore', [PageController::class, 'restore'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['POST', '/admin/pages/force-delete', [PageController::class, 'forceDelete'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['POST', '/admin/pages/update-position', [PageController::class, 'updatePosition'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['POST', '/admin/pages/reorder', [PageController::class, 'reorder'], [AuthMiddleware::class, AdminMiddleware::class]],
    
    // Email Templates
    ['GET', '/admin/email-templates', [EmailTemplateController::class, 'index'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['GET', '/admin/email-templates/create', [EmailTemplateController::class, 'create'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['POST', '/admin/email-templates/store', [EmailTemplateController::class, 'store'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['GET', '/admin/email-templates/edit/{id}', [EmailTemplateController::class, 'edit'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['POST', '/admin/email-templates/update/{id}', [EmailTemplateController::class, 'update'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['POST', '/admin/email-templates/delete', [EmailTemplateController::class, 'delete'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['POST', '/admin/email-templates/toggle', [EmailTemplateController::class, 'toggle'], [AuthMiddleware::class, AdminMiddleware::class]],
    ['POST', '/admin/email-templates/restore', [EmailTemplateController::class, 'restore'], [AuthMiddleware::class, AdminMiddleware::class]],

    // File Structure
    ['GET', '/admin/file-structure', [FileStructureController::class, 'index'], [AuthMiddleware::class, AdminMiddleware::class]],

    // Install
    ['GET', '/install/success', [InstallController::class, 'success']],
];

foreach ($routes as $route) {
    $method = strtolower($route[0]);
    $path = $route[1];
    $handler = $route[2];
    $middlewares = $route[3] ?? [];

    $router->$method($path, $handler, $middlewares);
}
