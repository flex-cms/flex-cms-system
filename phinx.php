<?php

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$migrationPaths = ['%%PHINX_CONFIG_DIR%%/db/migrations'];

$pluginsDir = __DIR__ . '/plugins';

if (is_dir($pluginsDir)) {
    $plugins = array_diff(scandir($pluginsDir), ['.', '..']);

    foreach ($plugins as $plugin) {
        $pluginMigrationPath = $pluginsDir . '/' . $plugin . '/Database/Migrations';

        if (is_dir($pluginMigrationPath)) {
            $migrationPaths[] = $pluginMigrationPath;
        }
    }
}

return
[
    'paths' => [
        'migrations' => $migrationPaths,
        'seeds' => '%%PHINX_CONFIG_DIR%%/db/seeds'
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => 'development',
        'production' => [
            'adapter' => 'mysql',
            'host' => 'localhost',
            'name' => 'production_db',
            'user' => 'root',
            'pass' => '',
            'port' => '3306',
            'charset' => 'utf8',
        ],
        'development' => [
            'adapter' => 'mysql',
            'host' => $_ENV['DB_HOST'],
            'name' => $_ENV['DB_NAME'],
            'user' => $_ENV['DB_USER'],
            'pass' => $_ENV['DB_PASS'],
            'port' => '3306',
            'charset' => 'utf8mb4',
        ],
        'testing' => [
            'adapter' => 'mysql',
            'host' => 'localhost',
            'name' => 'testing_db',
            'user' => 'root',
            'pass' => '',
            'port' => '3306',
            'charset' => 'utf8',
        ]
    ],
    'version_order' => 'creation'
];