<?php

declare(strict_types=1);

use Dotenv\Dotenv;
use Illuminate\Database\Capsule\Manager as Capsule;

$rootPath = dirname(__DIR__);

if (!isset($_ENV['DB_HOST'])) {
    $dotenv = Dotenv::createImmutable($rootPath);
    $dotenv->load();
}

$requiredVariables = [
    'DB_HOST',
    'DB_NAME',
    'DB_USER',
    'DB_PASS',
    'DB_CHAR',
];

foreach ($requiredVariables as $variable) {
    if (!array_key_exists($variable, $_ENV)) {
        throw new RuntimeException(
            sprintf(
                'Липсва задължителната променлива "%s" в .env файла.',
                $variable
            )
        );
    }
}

$capsule = new Capsule();

$capsule->addConnection([
    'driver' => 'mysql',
    'host' => $_ENV['DB_HOST'],
    'port' => $_ENV['DB_PORT'] ?? '3306',
    'database' => $_ENV['DB_NAME'],
    'username' => $_ENV['DB_USER'],
    'password' => $_ENV['DB_PASS'],
    'charset' => $_ENV['DB_CHAR'],
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

return $capsule;
