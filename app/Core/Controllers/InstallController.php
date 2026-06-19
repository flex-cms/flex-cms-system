<?php

namespace Flex\Core\Controllers;

use Flex\Attributes\UseExceptions;
use Flex\Core\Routing\View;
use Illuminate\Database\Capsule\Manager;
use PDO;

class InstallController extends BaseController
{
    #[UseExceptions]
    public function index()
    {
        if (file_exists(base_path('storage/installed.lock'))) {
            header("Location: /admin");
            exit;
        }

        $data = [
            'title' => 'Създаване на базата данни'
        ];

        render_view('install/create', $data);
    }

    #[UseExceptions]
    public function processDb()
    {
        $db_host = $_POST['db_host'] ?? 'localhost';
        $db_name = $_POST['db_name'] ?? '';
        $db_user = $_POST['db_user'] ?? '';
        $db_pass = $_POST['db_pass'] ?? '';

        $pdo = new PDO("mysql:host=$db_host", $db_user, $db_pass);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name`");

        $dbSettings = [
            'DB_HOST'       => $db_host,
            'DB_NAME'       => $db_name,
            'DB_USER'       => $db_user,
            'DB_PASS'       => $db_pass,
            'DB_CHAR'       => 'utf8mb4',
            'ADMIN_EMAIL'   => $_POST['admin_email'],
            'ADMIN_PASS'    => $_POST['admin_pass'],
        ];

        $this->updateEnvFile($dbSettings);
        $this->runMigrations();

        $capsule = new Manager;
        $capsule->addConnection([
            'driver' => 'mysql',
            'host' => $db_host,
            'database' => $db_name,
            'username' => $db_user,
            'password' => $db_pass,
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ]);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        $_SESSION['install_success'] = ['email' => $_POST['admin_email'], 'password' => $_POST['admin_pass']];
        file_put_contents(base_path('storage/installed.lock'), date('Y-m-d H:i:s'));

        View::redirect('/install/success');
    }

    #[UseExceptions]
    public function success()
    {
        if (!file_exists(base_path('storage/installed.lock'))) {
            header("Location: /install");
            exit;
        }

        if (!isset($_SESSION['install_success'])) {
            header("Location: /admin");
            exit;
        }

        $data = $_SESSION['install_success'];
        unset($_SESSION['install_success']);

        $data = [
            'title' => 'Инсталацията е успешна',
            'admin_email' => $data['email'],
            'admin_password' => $data['password']
        ];
        
        render_view('install/success', $data);
    }

    #[UseExceptions]
    private function runMigrations()
    {
        $migrate = shell_exec('php vendor/bin/phinx migrate -c phinx.php 2>&1');
        $seed = shell_exec('php vendor/bin/phinx seed:run -c phinx.php 2>&1');

        if (strpos($migrate, 'error') !== false || strpos($seed, 'error') !== false) {
            throw new \Exception("Грешка при миграциите: " . $migrate . $seed);
        }
    }

    private function updateEnvFile(array $data)
    {
        $envPath = base_path('.env');
        $examplePath = base_path('.env.example');

        if (!file_exists($envPath) && file_exists($examplePath)) {
            copy($examplePath, $envPath);
        }

        $envContent = file_get_contents($envPath);

        foreach ($data as $key => $value) {
            if (preg_match("/^$key=.*$/m", $envContent)) {
                $envContent = preg_replace("/^$key=.*$/m", "$key=$value", $envContent);
            } else {
                $envContent .= "\n$key=$value";
            }
        }

        file_put_contents($envPath, $envContent);
    }
}