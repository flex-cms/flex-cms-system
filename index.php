<?php

session_start();

require_once 'vendor/autoload.php';
require_once 'functions.php';

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
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    $capsule = new Capsule;
    $capsule->addConnection([
        'driver'    => 'mysql',
        'host'      => $_ENV['DB_HOST'],
        'database'  => $_ENV['DB_NAME'],
        'username'  => $_ENV['DB_USER'],
        'password'  => $_ENV['DB_PASS'],
        'charset'   => $_ENV['DB_CHAR'],
        'collation' => 'utf8mb4_unicode_ci',
        'prefix'    => '',
    ]);
    $capsule->setAsGlobal();
    $capsule->bootEloquent();
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
    $debugMode = Setting::get('debug_mode', false);
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

$pluginManager = new PluginManager($events, $activePlugins);
$router->setPluginManager($pluginManager);
$pluginManager->loadPlugins($router);

$activeTheme = Setting::get('active_theme', null);
$themePath = __DIR__ . '/themes/' . $activeTheme;

if (is_dir($themePath)) {
    define('ACTIVE_THEME', $activeTheme);
    $themeClass = "Themes\\" . $activeTheme . "\\ThemeServiceProvider";
    if (class_exists($themeClass)) {
        $themeClass::init();
    }
} else {
    define('ACTIVE_THEME', null);
}

$content = "Здравей, това е съдържанието на сайта.";
$content = $events->applyFilters('the_content', $content);

$timezone = Setting::get('timezone', 'Europe/Sofia');
date_default_timezone_set($timezone);

require_once __DIR__ . '/app/routes.php';
$router->resolve();
