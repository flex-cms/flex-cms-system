<?php

namespace Flex\Core\Controllers;

use Flex\Core\Events\EventManager;
use Flex\Core\Plugins\PluginManager;
use Flex\Models\Plugin;
use Flex\Core\Routing\View;

class PluginController extends BaseController
{
    protected EventManager $events;
    protected PluginManager $pluginManager;

    public function __construct(EventManager $events, PluginManager $pluginManager)
    {
        $this->events = $events;
        $this->pluginManager = $pluginManager;
    }

    public function index()
    {
        $pluginsPath = dirname(__DIR__, 3) . '/plugins';

        if (is_dir($pluginsPath)) {
            $folders = array_filter(glob($pluginsPath . '/*'), 'is_dir');

            foreach ($folders as $folder) {
                $slug = basename($folder);
                $manifestPath = $folder . '/plugin.json';

                $name = ucfirst(str_replace('-', ' ', $slug));
                $description = 'Управление на функционалности за ' . $slug;
                $version = '1.0.0';
                $manifest = [];

                if (file_exists($manifestPath)) {
                    $manifest = json_decode(file_get_contents($manifestPath), true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $name = $manifest['name'] ?? $name;
                        $description = $manifest['description'] ?? $description;
                        $version = $manifest['version'] ?? $version;
                    }
                }

                $plugin = Plugin::where('slug', $slug)->first();

                $author = $manifest['author'] ?? null;
                $authorUrl = $manifest['author_url'] ?? null;
                $requires = $manifest['requires'] ?? null;

                if (!$plugin) {
                    Plugin::create([
                        'name' => $name,
                        'slug' => $slug,
                        'description' => $description,
                        'author' => $author,
                        'author_url' => $authorUrl,
                        'requires' => $requires,
                        'is_active' => false,
                        'version' => $version
                    ]);
                } else {
                    $dbRequires = is_string($plugin->requires) ? json_decode($plugin->requires, true) : $plugin->requires;

                    if (
                        $plugin->version !== $version ||
                        $plugin->name !== $name ||
                        $plugin->description !== $description ||
                        $plugin->author !== $author ||
                        ($plugin->author_url ?? null) !== $authorUrl ||
                        $dbRequires !== $requires
                    ) {
                        Plugin::where('slug', $slug)->update([
                            'name' => $name,
                            'description' => $description,
                            'version' => $version,
                            'author' => $author,
                            'author_url' => $authorUrl,
                            'requires' => $requires
                        ]);
                    }
                }
            }
        }

        $query = Plugin::query();

        if (!empty($_GET['search'])) {
            $search = trim($_GET['search']);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('slug', 'LIKE', "%{$search}%");
            });
        }

        if (!empty($_GET['status'])) {
            $status = $_GET['status'];
            if ($status === 'active') {
                $query->where('is_active', 1);
            } elseif ($status === 'inactive') {
                $query->where('is_active', 0);
            }
        }

        $sort = $_GET['sort'] ?? 'name';
        $direction = $_GET['direction'] ?? 'asc';

        if (in_array($sort, ['name', 'is_active'])) {
            $query->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');
        } else {
            $query->orderBy('name', 'asc');
        }

        $plugins = $query->get();

        return $this->render(View::make('admin/plugins/index', [
            'title' => 'Управление на Плъгини',
            'plugins' => $plugins
        ], 'admin'));
    }

    public function toggle()
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

        $plugin->is_active = !$plugin->is_active;
        $plugin->save();

        $this->pluginManager->initSinglePlugin($plugin->slug);

        $plugin->is_active
            ? $this->events->trigger("plugin.activated.{$plugin->slug}")
            : $this->events->trigger("plugin.deactivated.{$plugin->slug}");

        $statusText = $plugin->is_active ? 'активиран' : 'деактивиран';
        return $this->jsonResponse(true, "Плъгинът беше {$statusText} успешно!");
    }

    public function delete()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? null;

        if (!$id) {
            return $this->jsonResponse(false, 'Невалидно ID за изтриване.');
        }

        $plugin = Plugin::find($id);
        if (!$plugin) {
            return $this->jsonResponse(false, 'Плъгинът вече е премахнат или не съществува.');
        }

        $this->pluginManager->initSinglePlugin($plugin->slug);

        $this->events->trigger("plugin.deleted.{$plugin->slug}");

        $plugin->delete();

        return $this->jsonResponse(true, "Плъгинът беше премахнат успешно от системата.");
    }

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

        $pluginsPath = dirname(__DIR__, 3) . '/plugins';
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

            $newManifestPath = $targetPluginPath . '/plugin.json';
            if (file_exists($newManifestPath)) {
                $newManifest = json_decode(file_get_contents($newManifestPath), true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $plugin->version = $newManifest['version'] ?? $plugin->version;
                    $plugin->name = $newManifest['name'] ?? $plugin->name;
                    $plugin->description = $newManifest['description'] ?? $plugin->description;
                    $plugin->save();
                }
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
        if (!is_dir($dir))
            return;
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->deleteDirectory("$dir/$file") : unlink("$dir/$file");
        }
        rmdir($dir);
    }

    private function jsonResponse(bool $success, string $message)
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => $success, 'message' => $message]);
        exit;
    }
}
