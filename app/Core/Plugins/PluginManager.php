<?php

namespace Flex\Core\Plugins;

use Flex\Core\Events\EventManager;
use Flex\Core\Routing\Router;

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
}