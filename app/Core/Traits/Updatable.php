<?php

namespace Flex\Core\Traits;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use ZipArchive;

trait Updatable
{
    protected function fetchLatestVersionData()
    {
        $url = $_ENV['UPDATE_DOMAIN'] ?? '';

        $username = $_ENV['UPDATE_SERVER_USER'] ?? '';
        $password = $_ENV['UPDATE_SERVER_PASS'] ?? '';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return null;
        }

        return json_decode($response, true);
    }

    protected function getVersionData()
    {
        $versionFile = base_path('version.php');

        if (!file_exists($versionFile)) {
            throw new \Exception("Файлът version.php не е намерен!");
        }

        return include($versionFile);
    }

    public function checkVersionComparison($latestVersion)
    {
        $data = $this->getVersionData();
        $currentVersionString = $data['version'] ?? '0.0.0';

        return version_compare($latestVersion, $currentVersionString, '>');
    }

    protected function downloadUpdate(string $url, string $savePath): bool
    {
        $fileHandle = fopen($savePath, 'w+');
        if (!$fileHandle)
            return false;

        $ch = curl_init($url);

        $username = $_ENV['UPDATE_SERVER_USER'] ?? '';
        $password = $_ENV['UPDATE_SERVER_PASS'] ?? '';

        if (!empty($username) && !empty($password)) {
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
        }

        curl_setopt($ch, CURLOPT_FILE, $fileHandle);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_USERAGENT, 'FlexCore-Updater/1.0');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Cache-Control: no-cache']);
        $success = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);
        fclose($fileHandle);

        if (!$success || $httpCode !== 200) {
            error_log("DEBUG: cURL failed. Code: $httpCode, Error: $error");
            @unlink($savePath);
            return false;
        }

        return true;
    }

    protected function verifyIntegrity(string $filePath, string $expectedHash): bool
    {
        if (!file_exists($filePath)) {
            error_log("DEBUG: Файлът за проверка не съществува: {$filePath}");
            return false;
        }

        if (strpos($expectedHash, 'sha256:') === 0) {
            $expectedHash = substr($expectedHash, 7);
        }

        $actualHash = hash_file('sha256', $filePath);

        error_log("DEBUG: Очакван хеш: " . strtolower($expectedHash));
        error_log("DEBUG: Реален хеш:  " . strtolower($actualHash));

        if (hash_equals(strtolower($expectedHash), strtolower($actualHash))) {
            return true;
        }

        @unlink($filePath);
        return false;
    }

    protected function extractUpdate(string $zipPath, string $extractPath): bool
    {
        if (!class_exists('ZipArchive')) {
            throw new \Exception(
                "Грешка: Разширението 'php-zip' не е инсталирано или не е активирано в php.ini. " .
                "Моля, инсталирайте го на сървъра си, за да продължите с обновяването."
            );
        }

        if (!file_exists($zipPath)) {
            throw new \Exception("Файлът не съществува на: {$zipPath}");
        }

        if (!is_readable($zipPath)) {
            throw new \Exception("Файлът съществува, но нямам права за четене: {$zipPath}");
        }

        $zip = new ZipArchive();

        $res = $zip->open($zipPath, ZipArchive::CHECKCONS);

        if ($res !== true) {

            $errors = [
                ZipArchive::ER_NOZIP => 'Това не е валиден ZIP архив.',
                ZipArchive::ER_NOENT => 'Файлът не е намерен.',
                ZipArchive::ER_OPEN => 'Не може да се отвори файла.',
                ZipArchive::ER_READ => 'Грешка при четене на архива.'
            ];

            $msg = $errors[$res] ?? "Неизвестна грешка ($res)";
            throw new \Exception("ZipArchive грешка: {$msg} (Път: {$zipPath})");
        }

        if (!$zip->extractTo($extractPath)) {
            $error = $zip->getStatusString();
            $zip->close();
            throw new \Exception("Грешка при extractTo: {$error}");
        }

        $zip->close();
        return true;
    }

    protected function runPendingMigrations(): void
    {
        $phinx = new \Phinx\Console\PhinxApplication();
        $command = $phinx->find('migrate');

        $arguments = [
            'command' => 'migrate',
            '--configuration' => base_path('phinx.php'),
            '--environment' => 'development',
        ];

        $input = new ArrayInput($arguments);
        $output = new BufferedOutput();

        try {
            $command->run($input, $output);
            error_log("DEBUG: Phinx Migrations executed: " . $output->fetch());
        } catch (\Exception $e) {
            error_log("DEBUG: Phinx Migration Error: " . $e->getMessage());
        }
    }
}
