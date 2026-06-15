<?php

namespace Flex\Core\Controllers;

use Flex\Attributes\UseExceptions;
use Flex\Core\Routing\View;
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

        render_view(View::make('install/create', ['title' => 'Инсталация - Стъпка 1'], 'install'));
    }

    #[UseExceptions]
    public function processDb()
    {
        $db_host = $_POST['db_host'] ?? 'localhost';
        $db_name = $_POST['db_name'] ?? '';
        $db_user = $_POST['db_user'] ?? '';
        $db_pass = $_POST['db_pass'] ?? '';

        try {
            $pdo = new PDO("mysql:host=$db_host", $db_user, $db_pass);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name`");

            $dbSettings = [
                'DB_HOST' => $db_host,
                'DB_NAME' => $db_name,
                'DB_USER' => $db_user,
                'DB_PASS' => $db_pass,
                'DB_CHAR' => 'utf8mb4'
            ];

            $this->updateEnvFile($dbSettings);

            $this->runMigrations();

            session_start();
            $_SESSION['install_success'] = [
                'email' => 'admin@flex-cms.com',
                'password' => 'admin123'
            ];

            file_put_contents(base_path('storage/installed.lock'), date('Y-m-d H:i:s'));

            header("Location: /install/success");
            exit;
        } catch (\Exception $e) {
            die("Грешка при инсталацията: " . $e->getMessage());
        }
    }

    #[UseExceptions]
    public function success()
    {
        if (!file_exists(base_path('storage/installed.lock'))) {
            header("Location: /install");
            exit;
        }

        session_start();
        if (!isset($_SESSION['install_success'])) {
            header("Location: /admin");
            exit;
        }

        $data = $_SESSION['install_success'];
        unset($_SESSION['install_success']);

        render_view(View::make('install/success', [
            'title' => 'Инсталацията е успешна',
            'admin_email' => $data['email'],
            'admin_password' => $data['password']
        ], 'install'));
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