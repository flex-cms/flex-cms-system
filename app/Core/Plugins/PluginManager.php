<?php

namespace Flex\Core\Plugins;

use Flex\Core\Events\EventManager;
use Flex\Core\Routing\Router;
use Flex\Core\Services\PluginDatabaseService;

class PluginManager
{
    protected $events;
    protected $pluginsPath;
    protected $activePlugins = [];
    protected $assetsToRender = ['styles' => [], 'scripts' => []];

    public function __construct(EventManager $events, array $activePlugins = [])
    {
        $this->events = $events;
        $this->activePlugins = $activePlugins;
        $this->pluginsPath = dirname(__DIR__, 3) . '/plugins';
    }

    public function initSinglePlugin(string $slug): void
    {
        $pluginPath = $this->pluginsPath . '/' . $slug;
        $pluginFile = $pluginPath . '/plugin.php';

        if (file_exists($pluginFile)) {
            $loader = require dirname(__DIR__, 3) . '/vendor/autoload.php';
            $namespacePart = str_replace(' ', '', ucwords(str_replace('-', ' ', $slug)));
            $fullNamespace = "Plugins\\" . $namespacePart . "\\";

            $loader->addPsr4($fullNamespace, $pluginPath . '/src');

            $eventManager = $this->events;

            include_once $pluginFile;
        }
    }

    public static function activate(string $slug): void
    {
        $pluginsPath = rtrim(plugins_path(), '/') . '/';
        $pluginPath = $pluginsPath . $slug;

        $loader = require dirname(__DIR__, 3) . '/vendor/autoload.php';
        $namespacePart = str_replace(' ', '', ucwords(str_replace('-', ' ', $slug)));
        $fullNamespace = "Plugins\\" . $namespacePart . "\\";
        $loader->addPsr4($fullNamespace, $pluginPath . '/src');

        $version = '1.0.0';
        $manifestPath = $pluginPath . '/plugin.json';
        if (file_exists($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true);
            if (isset($manifest['version'])) {
                $version = $manifest['version'];
            }
        }

        $sqlPath = $pluginPath . "/database/migrations/{$version}.sql";

        if (file_exists($sqlPath)) {
            $dbService = new PluginDatabaseService();
            $dbService->executeSqlFile($slug, $sqlPath);
        }

        $installerClass = $fullNamespace . "Installer";

        if (class_exists($installerClass) && method_exists($installerClass, 'install')) {
            $installerClass::install();
        }
    }

    public function loadPlugins(Router $router): void
    {
        $loader = require dirname(__DIR__, 3) . '/vendor/autoload.php';
        $currentUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

        foreach ($this->activePlugins as $pluginDir) {
            $pluginPath = $this->pluginsPath . '/' . $pluginDir;
            $pluginFile = $pluginPath . '/plugin.php';

            if (file_exists($pluginFile)) {
                $namespacePart = str_replace(' ', '', ucwords(str_replace('-', ' ', $pluginDir)));
                $fullNamespace = "Plugins\\" . $namespacePart . "\\";

                $loader->addPsr4($fullNamespace, $pluginPath . '/src');

                $this->collectPluginAssets($pluginDir, $currentUri);

                $eventManager = $this->events;
                include_once $pluginFile;
            }
        }

        $this->registerAssetHooks();

        $this->events->trigger('plugins_loaded');
    }

    protected function collectPluginAssets(string $pluginDir, string $currentUri): void
    {
        $manifest = $this->getManifest($pluginDir);

        if (empty($manifest['assets'])) {
            return;
        }

        $assets = $manifest['assets'];

        if (!empty($assets['styles'])) {
            foreach ($assets['styles'] as $style) {
                if ($this->shouldLoadAsset($currentUri, $style['only_on'] ?? [])) {
                    $this->assetsToRender['styles'][] = "/plugins/{$pluginDir}/{$style['src']}";
                }
            }
        }

        if (!empty($assets['scripts'])) {
            foreach ($assets['scripts'] as $script) {
                if ($this->shouldLoadAsset($currentUri, $script['only_on'] ?? [])) {
                    $this->assetsToRender['scripts'][] = "/plugins/{$pluginDir}/{$script['src']}";
                }
            }
        }
    }

    protected function shouldLoadAsset(string $currentUri, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            $regex = '#^' . str_replace('\*', '.*', preg_quote($pattern, '#')) . '$#';

            if (preg_match($regex, $currentUri)) {
                return true;
            }
        }
        return false;
    }

    protected function registerAssetHooks(): void
    {
        $this->events->listen('view.head', function () {
            foreach ($this->assetsToRender['styles'] as $styleUrl) {
                echo "    <link rel=\"stylesheet\" href=\"{$styleUrl}\">\n";
            }
        });

        $this->events->listen('view.footer', function () {
            foreach ($this->assetsToRender['scripts'] as $scriptUrl) {
                echo "    <script src=\"{$scriptUrl}\"></script>\n";
            }
        });
    }

    public function getAvailablePlugins(): array
    {
        if (!is_dir($this->pluginsPath)) {
            return [];
        }

        $plugins = array_diff(scandir($this->pluginsPath), ['..', '.']);

        return array_values(array_filter($plugins, function ($item) {
            return is_dir($this->pluginsPath . '/' . $item);
        }));
    }

    public function getManifest(string $pluginDir): array
    {
        $manifestPath = $this->pluginsPath . '/' . $pluginDir . '/plugin.json';

        if (file_exists($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $manifest;
            }
        }

        return [];
    }

    public function deletePlugin(string $slug, bool $dropTables = false): void
    {
        $pluginsPath = rtrim($this->pluginsPath, '/\\') . DIRECTORY_SEPARATOR;
        $pluginPath = $pluginsPath . $slug;

        $pluginPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $pluginPath);

        if ($dropTables) {
            $uninstallSqlPath = $pluginPath . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'uninstall.sql';

            if (file_exists($uninstallSqlPath)) {
                $dbService = new PluginDatabaseService();
                $dbService->executeSqlFile($slug, $uninstallSqlPath);
            }
        }

        $this->initSinglePlugin($slug);

        $this->events->trigger("plugin.deleted.{$slug}");

        if (is_dir($pluginPath)) {
            $this->deleteDirectory($pluginPath);
        }
    }

    private function deleteDirectory(string $dir): void
    {
        $dir = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $dir), DIRECTORY_SEPARATOR);

        if (!is_dir($dir)) {
            return;
        }

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            pclose(popen('cmd.exe /c rd /s /q ' . escapeshellarg($dir), 'r'));
        } else {
            pclose(popen('rm -rf ' . escapeshellarg($dir), 'r'));
        }

        if (is_dir($dir)) {
            usleep(100000);
        }
    }
}
