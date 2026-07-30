<?php

use Illuminate\Database\Capsule\Manager;

session_start();

require_once 'vendor/autoload.php';
require_once 'functions.php';

require_once 'version.php';

use Dotenv\Dotenv;
use Flex\Core\Controllers\InstallController;
use Flex\Core\Database;
use Flex\Core\Events\EventManager;
use Flex\Core\Plugins\PluginManager;
use Flex\Core\Routing\Router;
use Flex\Models\Setting;
use Illuminate\Database\Capsule\Manager as Capsule;

$isInstalled = file_exists(base_path('storage/installed.lock'));

if ($isInstalled) {
    require __DIR__ . '/bootstrap/app.php';
}

if (!$isInstalled) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    ini_set('error_log', base_path('storage/logs/php_debug.log'));
    
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    
    if (!in_array($uri, ['/install', '/install/process-db', '/install/success'])) {
        header("Location: /install");
        exit;
    }

    $events = EventManager::getInstance();
    $router = new Router($events);
    
    $router->get('/install', [InstallController::class, 'index']);
    $router->post('/install/process-db', [InstallController::class, 'processDb']);
    
    $router->resolve();
    exit;
}

function db() { return Database::getInstance(); }
db();

try {
    $debugMode = Setting::getValue('debug_mode', false);
} catch (\Exception $e) {
    $debugMode = false;
}

if ($debugMode) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    ini_set('error_log', base_path('storage/logs/php_debug.log'));
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

$events = EventManager::getInstance();
$router = new Router($events);

try {
    $activePlugins = Capsule::table('plugins')->where('is_active', 1)->pluck('slug')->toArray();
} catch (\Exception $e) {
    $activePlugins = [];
}

$connection = Manager::connection();

$pluginManager = new PluginManager(
    $events,
    $connection,
    $activePlugins
);
$router->setPluginManager($pluginManager);
$pluginManager->loadPlugins($router);

$activeTheme = Setting::getValue('active_theme', null);
$themePath = __DIR__ . '/themes/' . $activeTheme;
define('ACTIVE_THEME', $activeTheme);

if ($activeTheme && is_dir($themePath)) {
    $themeBootstrap = $themePath . '/index.php';
    if (file_exists($themeBootstrap)) {
        require_once $themeBootstrap;
    }
}

$content = "Здравей, това е съдържанието на сайта.";
$content = $events->applyFilters('the_content', $content);

$timezone = Setting::getValue('timezone', 'Europe/Sofia');
date_default_timezone_set($timezone);

require_once __DIR__ . '/app/routes.php';
$router->resolve();
