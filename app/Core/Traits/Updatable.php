<?php

namespace Flex\Core\Traits;

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
        $directory = dirname($savePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $fileHandle = fopen($savePath, 'w+');
        if (!$fileHandle) {
            return false;
        }

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_FILE, $fileHandle);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_USERAGENT, 'FlexCore-Updater/1.0');

        $success = curl_exec($ch);

        $error = curl_error($ch);

        curl_close($ch);
        fclose($fileHandle);

        if (!$success) {
            @unlink($savePath);
            error_log("Update download error: " . $error);
            return false;
        }

        return true;
    }

    protected function verifyIntegrity(string $filePath, string $expectedHash): bool
    {
        if (!file_exists($filePath)) {
            error_log("Integrity check failed: File does not exist at {$filePath}");
            return false;
        }

        $actualHash = hash_file('sha256', $filePath);

        if (hash_equals($expectedHash, $actualHash)) {
            return true;
        }

        error_log("Integrity check failed! Expected: {$expectedHash}, Actual: {$actualHash}");

        @unlink($filePath);

        return false;
    }

    protected function extractUpdate(string $zipPath, string $extractPath): bool
    {
        $zip = new \ZipArchive();

        if ($zip->open($zipPath) !== true) {
            error_log("Failed to open update archive: {$zipPath}");
            return false;
        }

        if (!$zip->extractTo($extractPath)) {
            error_log("Failed to extract update files to: {$extractPath}");
            $zip->close();
            return false;
        }

        $zip->close();
        return true;
    }

    protected function runPendingMigrations(): void
    {
        $migrationsPath = base_path('database/migrations');

        $files = glob($migrationsPath . '/*.php');
        sort($files);

        foreach ($files as $file) {
            $migrationName = basename($file, '.php');

            if (!$this->isMigrationApplied($migrationName)) {
                require_once $file;

                $className = $this->getClassFromFileName($migrationName);
                if (class_exists($className)) {
                    $migration = new $className();
                    $migration->up();

                    $this->markMigrationAsApplied($migrationName);
                }
            }
        }
    }
}