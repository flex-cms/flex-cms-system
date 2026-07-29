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
use Flex\Core\Controllers\MenuController;

// Auth Routes
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/logout', [AuthController::class, 'logout']);
$router->get('/password/forgot', [AuthController::class, 'showForgotPassword']);
$router->post('/password/forgot', [AuthController::class, 'forgotPassword']);
$router->get('/password/reset', [AuthController::class, 'showResetPassword']);
$router->post('/password/reset', [AuthController::class, 'resetPassword']);

// Dashboard & UI
$router->get('/admin/dashboard', [AdminController::class, 'index'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/sidebar-toggle', [AdminController::class, 'toggleSidebar'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/theme-toggle', [AdminController::class, 'toggleTheme'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/ui/save-state', [AdminController::class, 'saveUiState'], [AuthMiddleware::class, AdminMiddleware::class]);

// Users
$router->get('/admin/users/index', [UserController::class, 'index'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->get('/admin/users/create', [UserController::class, 'create'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/users/store', [UserController::class, 'store'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->get('/admin/users/edit/{id}', [UserController::class, 'edit'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/users/update/{id}', [UserController::class, 'update'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/users/delete/{id}', [UserController::class, 'delete'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/users/force-delete', [UserController::class, 'forceDelete'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/users/toggle', [UserController::class, 'toggle'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/users/update-position', [UserController::class, 'updatePosition'], [AuthMiddleware::class, AdminMiddleware::class]);

// Menus
$router->get('/admin/menus', [MenuController::class, 'index'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->get('/admin/menus/create', [MenuController::class, 'create'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->get('/admin/menus/items/{id}', [MenuController::class, 'getItems'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->get('/admin/menus/edit/{id}', [MenuController::class, 'edit'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/menus/store', [MenuController::class, 'store'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/menus/update/{id}', [MenuController::class, 'update'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/menus/delete', [MenuController::class, 'delete'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/menus/force-delete', [MenuController::class, 'forceDelete'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/menus/restore', [MenuController::class, 'restore'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/menus/toggle', [MenuController::class, 'toggle'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/menus/items/{id}/tree-update', [MenuController::class, 'updateTreePosition'], [AuthMiddleware::class, AdminMiddleware::class]);

// Roles
$router->get('/admin/users/roles', [RoleController::class, 'index'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->get('/admin/users/roles/create', [RoleController::class, 'create'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/users/roles/create', [RoleController::class, 'store'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->get('/admin/users/roles/edit/{id}', [RoleController::class, 'edit'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/users/roles/edit/{id}', [RoleController::class, 'update'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/users/roles/toggle', [RoleController::class, 'toggle'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/users/roles/delete', [RoleController::class, 'delete'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/users/roles/force-delete', [RoleController::class, 'forceDelete'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/users/roles/restore', [RoleController::class, 'restore'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/users/roles/update-position', [RoleController::class, 'updatePosition'], [AuthMiddleware::class, AdminMiddleware::class]);

// Permissions
$router->get('/admin/users/permissions', [PermissionController::class, 'index'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->get('/admin/users/permissions/create', [PermissionController::class, 'create'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/users/permissions/store', [PermissionController::class, 'store'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->get('/admin/users/permissions/edit/{id}', [PermissionController::class, 'edit'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/users/permissions/update/{id}', [PermissionController::class, 'update'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/users/permissions/delete', [PermissionController::class, 'delete'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/users/permissions/force-delete', [PermissionController::class, 'forceDelete'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/users/permissions/restore', [PermissionController::class, 'restore'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/users/permissions/toggle', [PermissionController::class, 'toggle'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/users/permissions/update-position', [PermissionController::class, 'updatePosition'], [AuthMiddleware::class, AdminMiddleware::class]);

// Settings
$router->get('/admin/settings/{group}', [SettingsController::class, 'show'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/settings/{group}/update', [SettingsController::class, 'update'], [AuthMiddleware::class, AdminMiddleware::class]);

// Updates
$router->get('/admin/update', [UpdateController::class, 'index'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/update/process', [UpdateController::class, 'process'], [AuthMiddleware::class, AdminMiddleware::class]);

// Plugins
$router->get('/admin/plugins', [PluginController::class, 'index'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/plugins/install', [PluginController::class, 'install'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/plugins/toggle', [PluginController::class, 'toggle'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/plugins/delete', [PluginController::class, 'delete'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/plugins/update', [PluginController::class, 'update'], [AuthMiddleware::class, AdminMiddleware::class]);

// Themes
$router->get('/admin/themes/all', [ThemeController::class, 'index'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/themes/activate', [ThemeController::class, 'activate'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/themes/deactivate', [ThemeController::class, 'deactivate'], [AuthMiddleware::class, AdminMiddleware::class]);

// Pages
$router->get('/admin/pages', [PageController::class, 'index'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->get('/admin/pages/create', [PageController::class, 'create'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/pages/store', [PageController::class, 'store'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->get('/admin/pages/edit/{id}', [PageController::class, 'edit'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/pages/delete', [PageController::class, 'delete'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/pages/update/{id}', [PageController::class, 'update'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/pages/toggle', [PageController::class, 'toggle'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/pages/restore', [PageController::class, 'restore'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/pages/force-delete', [PageController::class, 'forceDelete'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/pages/reorder', [PageController::class, 'reorder'], [AuthMiddleware::class, AdminMiddleware::class]);

// Email Templates
$router->get('/admin/email-templates', [EmailTemplateController::class, 'index'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->get('/admin/email-templates/create', [EmailTemplateController::class, 'create'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/email-templates/store', [EmailTemplateController::class, 'store'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->get('/admin/email-templates/edit/{id}', [EmailTemplateController::class, 'edit'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/email-templates/update/{id}', [EmailTemplateController::class, 'update'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/email-templates/delete', [EmailTemplateController::class, 'delete'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/email-templates/toggle', [EmailTemplateController::class, 'toggle'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/email-templates/restore', [EmailTemplateController::class, 'restore'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/email-templates/force-delete', [EmailTemplateController::class, 'forceDelete'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/email-templates/update-position', [EmailTemplateController::class, 'updatePosition'], [AuthMiddleware::class, AdminMiddleware::class]);

// File Structure
$router->get('/admin/file-structure', [FileStructureController::class, 'index'], [AuthMiddleware::class, AdminMiddleware::class]);

// Install
$router->get('/install/success', [InstallController::class, 'success']);
