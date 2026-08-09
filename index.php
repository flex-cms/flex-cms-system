<?php

use Illuminate\Database\Capsule\Manager;

session_start();

require_once 'vendor/autoload.php';
require_once 'functions.php';
require_once 'version.php';

use Flex\Core\Controllers\InstallController;
use Flex\Core\Database;
use Flex\Core\Events\EventManager;
use Flex\Core\Http\Request;
use Flex\Core\Http\ResponseEmitter;
use Flex\Core\Plugins\PluginManager;
use Flex\Core\Routing\FlexRouterApplication;
use Flex\Core\Routing\Router;
use Flex\Models\Setting;
use Illuminate\Database\Capsule\Manager as Capsule;
use Flex\Features\Authentication\Adapters\FlexAuthenticator;
use Flex\Features\Authentication\Adapters\FlexLoginUrlResolver;
use Flex\Features\Authentication\Contracts\AuthenticatorInterface;
use Flex\Features\Authentication\Contracts\LoginUrlResolverInterface;
use Flex\Features\Authentication\Middleware\Authenticate;
use Flex\Features\Authentication\Middleware\RequireAdmin;

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

    if (!in_array($uri, ['/install', '/install/process-db', '/install/success'], true)) {
        header('Location: /install');
        exit;
    }

    $events = EventManager::getInstance();
    $router = new Router($events);

    $router->get('/install', [InstallController::class, 'index']);
    $router->post('/install/process-db', [InstallController::class, 'processDb']);

    $router->resolve();
    exit;
}

function db()
{
    return Database::getInstance();
}

db();

try {
    $debugMode = (bool) Setting::getValue('debug_mode', false);
} catch (Throwable) {
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

$legacyRouter = new Router($events);

$router = $legacyRouter;
$GLOBALS['router'] = $legacyRouter;

try {
    $activePlugins = Capsule::table('plugins')
        ->where('is_active', 1)
        ->pluck('slug')
        ->toArray();
} catch (Throwable) {
    $activePlugins = [];
}

$connection = Manager::connection();

$pluginManager = new PluginManager(
    $events,
    $connection,
    $activePlugins
);

$legacyRouter->setPluginManager($pluginManager);
$pluginManager->loadPlugins($legacyRouter);

$activeTheme = Setting::getValue('active_theme', null);
$themePath = __DIR__ . '/themes/' . $activeTheme;
define('ACTIVE_THEME', $activeTheme);

if ($activeTheme && is_dir($themePath)) {
    $themeBootstrap = $themePath . '/index.php';

    if (is_file($themeBootstrap)) {
        require_once $themeBootstrap;
    }
}

$content = 'Здравей, това е съдържанието на сайта.';
$content = $events->applyFilters('the_content', $content);

$timezone = Setting::getValue('timezone', 'Europe/Sofia');
date_default_timezone_set($timezone);

$scriptPath = str_replace(
    '\\',
    '/',
    dirname($_SERVER['SCRIPT_NAME'] ?? '/')
);
$basePath = $scriptPath === '/' ? '' : $scriptPath;

$flexApplication = FlexRouterApplication::create(
    baseUrl: rtrim(base_url(), '/'),
    basePath: $basePath,
    passNotFound: true,
    debug: $debugMode,
    logger: static function (Throwable $exception): void {
        error_log(sprintf(
            '[FlexRouter] %s: %s in %s:%d',
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
        ));
    },
);

$flexApplication->container->instance(
    AuthenticatorInterface::class,
    new FlexAuthenticator(),
);

$flexApplication->container->instance(
    LoginUrlResolverInterface::class,
    new FlexLoginUrlResolver(),
);

$flexApplication->middleware
    ->alias('auth', Authenticate::class)
    ->alias('admin', RequireAdmin::class);

$flexApplication
    ->featureRoutes(base_path('app/Features'))
    ->load(['web', 'admin', 'api']);

$router = $legacyRouter;
$GLOBALS['router'] = $legacyRouter;

require __DIR__ . '/app/routes.php';

$request = Request::fromGlobals();
$flexResult = $flexApplication->kernel->handle($request);

if ($flexResult->isHandled()) {
    (new ResponseEmitter())->emit($flexResult->response());
    exit;
}

$legacyRouter->resolve();
