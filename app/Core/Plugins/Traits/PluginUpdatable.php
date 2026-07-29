<?php

namespace Flex\Core\Plugins\Traits;

use Flex\Models\Plugin;

trait PluginUpdatable
{
    public function update()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? null;

        if (!$id) {
            return $this->jsonResponse(false, 'Невалидно ID на плъгин.');
        }

        $plugin = Plugin::find($id);
        if (!$plugin) {
            return $this->jsonResponse(false, 'Плъгинът не е намерен.');
        }

        $pluginsPath = plugins_path();
        $manifestPath = $pluginsPath . '/' . $plugin->slug . '/plugin.json';

        if (!file_exists($manifestPath)) {
            return $this->jsonResponse(false, 'Манифестът (plugin.json) не беше намерен локално.');
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);
        $repoUrl = $manifest['repository'] ?? null;

        if (!$repoUrl) {
            return $this->jsonResponse(false, 'В plugin.json липсва линк към репозиториум ("repository").');
        }

        $zipUrl = rtrim($repoUrl, '/') . '/archive/refs/heads/main.zip';

        $tempZipFile = sys_get_temp_dir() . '/' . $plugin->slug . '_update.zip';

        $ch = curl_init($zipUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Flex-CMS-Core');
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        $zipContent = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$zipContent) {
            return $this->jsonResponse(false, 'Неуспешно изтегляне на пакета от GitHub. Сървърът върна код ' . $httpCode);
        }

        file_put_contents($tempZipFile, $zipContent);

        $zip = new \ZipArchive();
        if ($zip->open($tempZipFile) === true) {
            $extractPath = sys_get_temp_dir() . '/flex_extract_' . $plugin->slug;

            if (!is_dir($extractPath)) {
                mkdir($extractPath, 0755, true);
            }

            $zip->extractTo($extractPath);
            $zip->close();

            $extractedFolders = glob($extractPath . '/*', GLOB_ONLYDIR);
            if (empty($extractedFolders)) {
                return $this->jsonResponse(false, 'Невалидна структура на ZIP архива.');
            }
            $innerFolder = $extractedFolders[0];

            $targetPluginPath = $pluginsPath . '/' . $plugin->slug;
            $this->copyDirectory($innerFolder, $targetPluginPath);

            $this->deleteDirectory($extractPath);
            unlink($tempZipFile);

            $this->pluginManager->migrateUpdatedPlugin(
                $plugin->slug
            );

            $newManifest = json_decode(
                file_get_contents($targetPluginPath . '/plugin.json'),
                true
            );

            if (is_array($newManifest)) {
                Plugin::where('slug', $plugin->slug)->update([
                    'name' => $newManifest['name'] ?? $plugin->name,
                    'description' => $newManifest['description']
                        ?? $plugin->description,
                ]);
            }

            return $this->jsonResponse(true, 'Плъгинът беше актуализиран успешно до последна версия!');
        } else {
            return $this->jsonResponse(false, 'Грешка при отварянето на ZIP архива.');
        }
    }

    private function copyDirectory(string $source, string $target): void
    {
        if (!is_dir($target)) {
            mkdir($target, 0755, true);
        }

        $files = array_diff(scandir($source), ['.', '..']);
        foreach ($files as $file) {
            $srcFile = $source . '/' . $file;
            $dstFile = $target . '/' . $file;

            if (is_dir($srcFile)) {
                $this->copyDirectory($srcFile, $dstFile);
            } else {
                copy($srcFile, $dstFile);
            }
        }
    }

    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $dir = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $dir), DIRECTORY_SEPARATOR);

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            pclose(popen('cmd.exe /c rd /s /q ' . escapeshellarg($dir), 'r'));
        } else {
            $files = array_diff(scandir($dir), ['.', '..']);
            foreach ($files as $file) {
                $path = "$dir/$file";
                is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
            }
            rmdir($dir);
        }
    }
}