<?php

namespace Flex\Core\Plugins;

use Flex\Core\Events\EventManager;
use Flex\Core\Plugins\Contracts\PluginInstallerInterface;
use Flex\Core\Plugins\Contracts\PluginServiceProviderInterface;
use Flex\Core\Routing\Router;
use Flex\Core\Services\PluginDatabaseService;
use Flex\Models\Plugin;
use Flex\Core\Plugins\Migrations\PluginMigrationManager;
use Illuminate\Database\Connection;
use RuntimeException;

class PluginManager
{
    protected $events;
    protected $pluginsPath;
    protected $activePlugins = [];
    protected $assetsToRender = ['styles' => [], 'scripts' => []];
    protected array $providers = [];

    protected PluginManifestValidator $manifestValidator;
    protected ?Router $router = null;

    private PluginMigrationManager $migrationManager;

    public function __construct(
        EventManager $events,
        Connection $connection,
        array $activePlugins = [],
        ?PluginManifestValidator $manifestValidator = null,
        ?PluginMigrationManager $migrationManager = null
    ) {
        $this->events = $events;
        $this->activePlugins = $activePlugins;
        $this->pluginsPath = dirname(__DIR__, 3) . '/plugins';

        $this->manifestValidator = $manifestValidator
            ?? new PluginManifestValidator();

        $this->migrationManager = $migrationManager
            ?? new PluginMigrationManager($connection);
    }

    public function setRouter(Router $router): self
    {
        $this->router = $router;

        return $this;
    }

    public function initSinglePlugin(
        string $slug,
        ?Router $router = null
    ): bool {
        $router ??= $this->router;

        if (!$router) {
            throw new RuntimeException(
                'Router не е зададен в PluginManager.'
            );
        }

        $provider = $this->registerSinglePlugin(
            $slug,
            $router
        );

        if (!$provider) {
            return false;
        }

        $provider->boot();

        return true;
    }

    public function activate(string $slug): bool
    {
        $plugin = Plugin::where('slug', $slug)->first();

        if (!$plugin) {
            throw new RuntimeException(
                "Плъгинът „{$slug}“ не е инсталиран."
            );
        }

        if ((bool) $plugin->is_active) {
            return true;
        }

        $manifest = $this->getManifest($slug);

        if (!($manifest['manifest_valid'] ?? false)) {
            throw new RuntimeException(
                'Плъгинът не може да бъде активиран: '
                . implode(
                    ' ',
                    $manifest['manifest_errors'] ?? []
                )
            );
        }

        $pluginPath = $this->getPluginDirectory($slug);

        $this->registerPluginAutoload(
            $manifest,
            $pluginPath
        );

        $pendingMigrations = $this->migrationManager->pending(
            $slug,
            $pluginPath
            . DIRECTORY_SEPARATOR
            . 'database'
            . DIRECTORY_SEPARATOR
            . 'migrations'
        );

        if ($pendingMigrations !== []) {
            throw new RuntimeException(
                sprintf(
                    'Плъгинът има %d неизпълнени migration файла и не може да бъде активиран.',
                    count($pendingMigrations)
                )
            );
        }

        try {
            $this->runInstallerHook(
                $manifest,
                'activate'
            );

            Plugin::where('slug', $slug)->update([
                'is_active' => true,
            ]);

            if (!in_array($slug, $this->activePlugins, true)) {
                $this->activePlugins[] = $slug;
            }

            $this->events->trigger(
                "plugin.activated.{$slug}",
                [
                    'slug' => $slug,
                    'manifest' => $manifest,
                ]
            );

            return true;
        } catch (\Throwable $exception) {
            $this->events->trigger(
                "plugin.failed.{$slug}",
                [
                    'stage' => 'activate',
                    'exception' => $exception,
                ]
            );

            throw new RuntimeException(
                "Активирането на плъгина „{$slug}“ беше неуспешно: "
                . $exception->getMessage(),
                previous: $exception
            );
        }
    }

    public function deactivate(string $slug): bool
    {
        $plugin = Plugin::where('slug', $slug)->first();

        if (!$plugin) {
            throw new RuntimeException(
                "Плъгинът „{$slug}“ не е инсталиран."
            );
        }

        if (!(bool) $plugin->is_active) {
            return true;
        }

        $manifest = $this->getManifest($slug);
        $pluginPath = $this->getPluginDirectory($slug);

        $this->registerPluginAutoload(
            $manifest,
            $pluginPath
        );

        try {
            $this->runInstallerHook(
                $manifest,
                'deactivate'
            );

            Plugin::where('slug', $slug)->update([
                'is_active' => false,
            ]);

            $this->activePlugins = array_values(
                array_filter(
                    $this->activePlugins,
                    static fn(string $activeSlug): bool =>
                    $activeSlug !== $slug
                )
            );

            unset($this->providers[$slug]);

            $this->events->trigger(
                "plugin.deactivated.{$slug}",
                [
                    'slug' => $slug,
                    'reason' => 'manual',
                ]
            );

            return true;
        } catch (\Throwable $exception) {
            $this->events->trigger(
                "plugin.failed.{$slug}",
                [
                    'stage' => 'deactivate',
                    'exception' => $exception,
                ]
            );

            throw new RuntimeException(
                "Деактивирането на плъгина „{$slug}“ беше неуспешно: "
                . $exception->getMessage(),
                previous: $exception
            );
        }
    }

    public function uninstall(
        string $slug,
        ?UninstallOptions $options = null
    ): bool {
        $options ??= new UninstallOptions();

        $plugin = Plugin::where('slug', $slug)->first();

        if (!$plugin) {
            throw new RuntimeException(
                "Плъгинът „{$slug}“ не е инсталиран."
            );
        }

        if ((bool) $plugin->is_active) {
            $this->deactivate($slug);
        }

        $manifest = $this->getManifest($slug);
        $pluginPath = $this->getPluginDirectory($slug);

        $this->registerPluginAutoload(
            $manifest,
            $pluginPath
        );

        try {
            $this->runInstallerHook(
                manifest: $manifest,
                method: 'uninstall',
                arguments: [$options]
            );

            if ($options->deleteData) {
                $this->rollbackAllPluginMigrations(
                    $slug,
                    $pluginPath
                );
            }

            Plugin::where('slug', $slug)->update([
                'is_active' => false,
                'is_installed' => false,
                'version' => null,
            ]);

            unset($this->providers[$slug]);

            $this->events->trigger(
                "plugin.uninstalled.{$slug}",
                [
                    'slug' => $slug,
                    'options' => $options,
                    'data_removed' => $options->deleteData,
                    'settings_removed' => $options->deleteSettings,
                    'cache_removed' => $options->deleteCache,
                    'logs_removed' => $options->deleteLogs,
                    'uploads_removed' => $options->deleteUploads,
                ]
            );

            return true;
        } catch (\Throwable $exception) {
            $this->events->trigger(
                "plugin.failed.{$slug}",
                [
                    'stage' => 'uninstall',
                    'exception' => $exception,
                ]
            );

            throw new RuntimeException(
                "Деинсталирането на плъгина „{$slug}“ беше неуспешно: "
                . $exception->getMessage(),
                previous: $exception
            );
        }
    }

    public function deletePluginFiles(string $slug): void
    {
        $pluginPath = $this->getPluginDirectory($slug);

        $this->deleteDirectory($pluginPath);

        Plugin::where('slug', $slug)->delete();

        $this->events->trigger(
            "plugin.files_deleted.{$slug}",
            ['slug' => $slug]
        );
    }

    public function install(string $slug): bool
    {
        $manifest = $this->getManifest($slug);

        if (!($manifest['manifest_valid'] ?? false)) {
            throw new RuntimeException(
                'Плъгинът не може да бъде инсталиран: '
                . implode(
                    ' ',
                    $manifest['manifest_errors'] ?? []
                )
            );
        }

        $pluginPath = $this->getPluginDirectory($slug);

        $this->registerPluginAutoload(
            $manifest,
            $pluginPath
        );

        $plugin = Plugin::where('slug', $slug)->first();

        if ($plugin && (bool) $plugin->is_installed) {
            throw new RuntimeException(
                "Плъгинът „{$slug}“ вече е инсталиран."
            );
        }

        try {
            $this->runPluginMigrations(
                $slug,
                $manifest,
                $pluginPath
            );

            $this->runInstallerHook(
                $manifest,
                'install'
            );

            Plugin::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $manifest['name'],
                    'description' => $manifest['description'] ?? null,
                    'version' => $manifest['version'],
                    'is_active' => false,
                    'is_installed' => true,
                ]
            );

            $this->events->trigger(
                "plugin.installed.{$slug}",
                [
                    'slug' => $slug,
                    'manifest' => $manifest,
                ]
            );

            return true;
        } catch (\Throwable $exception) {
            $this->events->trigger(
                "plugin.failed.{$slug}",
                [
                    'stage' => 'install',
                    'exception' => $exception,
                ]
            );

            throw new RuntimeException(
                "Инсталирането на плъгина „{$slug}“ беше неуспешно: "
                . $exception->getMessage(),
                previous: $exception
            );
        }
    }

    private function getPluginDirectory(string $slug): string
    {
        $pluginPath = $this->pluginsPath
            . DIRECTORY_SEPARATOR
            . $slug;

        if (!is_dir($pluginPath)) {
            throw new RuntimeException(
                "Директорията на плъгина „{$slug}“ не съществува."
            );
        }

        return $pluginPath;
    }

    private function runPluginMigrations(
        string $slug,
        array $manifest,
        string $pluginPath
    ): void {
        if (!($manifest['migrations'] ?? false)) {
            return;
        }

        $this->migrationManager->migrate(
            pluginSlug: $slug,
            pluginVersion: (string) $manifest['version'],
            migrationsPath: $pluginPath
            . DIRECTORY_SEPARATOR
            . 'database'
            . DIRECTORY_SEPARATOR
            . 'migrations',
            tablePrefix: $this->makePluginTablePrefix($slug),
        );
    }

    private function makePluginTablePrefix(string $slug): string
    {
        return 'plugin_' . str_replace('-', '_', $slug);
    }

    private function runInstallerHook(
        array $manifest,
        string $method,
        array $arguments = []
    ): void {
        $installerClass = $manifest['installer'] ?? null;

        if (
            !is_string($installerClass)
            || $installerClass === ''
        ) {
            return;
        }

        if (!class_exists($installerClass)) {
            throw new RuntimeException(
                "Installer класът „{$installerClass}“ не съществува."
            );
        }

        if (
            !is_subclass_of(
                $installerClass,
                PluginInstallerInterface::class
            )
        ) {
            throw new RuntimeException(
                "Installer класът „{$installerClass}“ трябва да имплементира "
                . PluginInstallerInterface::class
            );
        }

        if (!method_exists($installerClass, $method)) {
            throw new RuntimeException(
                "Installer класът „{$installerClass}“ няма метод {$method}()."
            );
        }

        $installerClass::$method(...$arguments);
    }

    public function loadPlugins(Router $router): void
    {
        $this->router = $router;

        $currentUri = parse_url(
            $_SERVER['REQUEST_URI'] ?? '/',
            PHP_URL_PATH
        );

        foreach ($this->activePlugins as $pluginSlug) {
            $provider = $this->registerSinglePlugin(
                $pluginSlug,
                $router
            );

            if (!$provider) {
                continue;
            }

            $this->collectPluginAssets(
                $pluginSlug,
                $currentUri
            );
        }

        foreach ($this->providers as $slug => $provider) {
            try {
                $provider->boot();
            } catch (\Throwable $exception) {
                $this->deactivateInvalidPlugin(
                    $slug,
                    [
                        'Provider boot failed: ' .
                        $exception->getMessage(),
                    ]
                );

                $this->events->trigger(
                    "plugin.failed.{$slug}",
                    [
                        'stage' => 'boot',
                        'exception' => $exception,
                    ]
                );
            }
        }

        $this->registerAssetHooks();

        $this->events->trigger('plugins_loaded', [
            'providers' => $this->providers,
        ]);
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
        $pluginPath = $this->pluginsPath . '/' . $pluginDir;
        $manifestPath = $pluginPath . '/plugin.json';

        if (!is_file($manifestPath)) {
            return $this->normalizeManifest(
                [],
                $pluginDir,
                [
                    'valid' => false,
                    'errors' => [
                        'Файлът plugin.json не съществува.',
                    ],
                    'warnings' => [],
                ]
            );
        }

        $content = file_get_contents($manifestPath);

        if ($content === false) {
            return $this->normalizeManifest(
                [],
                $pluginDir,
                [
                    'valid' => false,
                    'errors' => [
                        'Файлът plugin.json не може да бъде прочетен.',
                    ],
                    'warnings' => [],
                ]
            );
        }

        $manifest = json_decode($content, true);

        if (!is_array($manifest)) {
            return $this->normalizeManifest(
                [],
                $pluginDir,
                [
                    'valid' => false,
                    'errors' => [
                        'Невалиден JSON: ' . json_last_error_msg(),
                    ],
                    'warnings' => [],
                ]
            );
        }

        $validation = $this->manifestValidator->validate(
            $manifest,
            $pluginDir,
            $pluginPath
        );

        return $this->normalizeManifest(
            $manifest,
            $pluginDir,
            $validation
        );
    }

    protected function normalizeManifest(
        array $manifest,
        string $pluginDir,
        array $validation
    ): array {
        $author = is_array($manifest['author'] ?? null)
            ? $manifest['author']
            : [];

        $requires = is_array($manifest['requires'] ?? null)
            ? $manifest['requires']
            : [];

        $routes = is_array($manifest['routes'] ?? null)
            ? $manifest['routes']
            : [];

        $autoload = is_array($manifest['autoload'] ?? null)
            ? $manifest['autoload']
            : [];

        return [
            'name' => (string) ($manifest['name'] ?? $pluginDir),
            'slug' => (string) ($manifest['slug'] ?? $pluginDir),
            'description' => (string) ($manifest['description'] ?? ''),
            'version' => (string) ($manifest['version'] ?? '0.0.0'),
            'type' => (string) ($manifest['type'] ?? 'plugin'),
            'license' => (string) ($manifest['license'] ?? ''),
            'homepage' => (string) ($manifest['homepage'] ?? ''),
            'repository' => (string) ($manifest['repository'] ?? ''),
            'provider' => (string) ($manifest['provider'] ?? ''),
            'installer' => (string) ($manifest['installer'] ?? ''),
            'author' => [
                'name' => (string) ($author['name'] ?? ''),
                'email' => (string) ($author['email'] ?? ''),
                'website' => (string) ($author['website'] ?? ''),
            ],
            'requires' => [
                'php' => (string) ($requires['php'] ?? ''),
                'flex' => (string) ($requires['flex'] ?? ''),
            ],
            'autoload' => $autoload,
            'routes' => $routes,
            'features' => array_values(
                is_array($manifest['features'] ?? null)
                ? $manifest['features']
                : []
            ),
            'permissions' => array_values(
                is_array($manifest['permissions'] ?? null)
                ? $manifest['permissions']
                : []
            ),
            'boot' => (bool) ($manifest['boot'] ?? false),
            'admin_menu' => $manifest['admin_menu'] ?? false,
            'migrations' => (bool) ($manifest['migrations'] ?? false),
            'seeders' => (bool) ($manifest['seeders'] ?? false),
            'assets' => $manifest['assets'] ?? false,
            'manifest_valid' => (bool) ($validation['valid'] ?? false),
            'manifest_errors' => array_values(
                $validation['errors'] ?? []
            ),
            'manifest_warnings' => array_values(
                $validation['warnings'] ?? []
            ),
            'directory' => $pluginDir,
        ];
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

    public function deactivateInvalidPlugins(): array
    {
        $deactivated = [];

        foreach ($this->getAvailablePlugins() as $pluginDir) {
            $manifest = $this->getManifest($pluginDir);

            if ($manifest['manifest_valid'] ?? false) {
                continue;
            }

            $errors = $manifest['manifest_errors'] ?? [];

            if (!$this->deactivateInvalidPlugin($pluginDir, $errors)) {
                continue;
            }

            $deactivated[] = [
                'slug' => $pluginDir,
                'errors' => $errors,
            ];
        }

        return $deactivated;
    }

    public function deactivateInvalidPlugin(
        string $slug,
        array $errors = []
    ): bool {
        $plugin = Plugin::where('slug', $slug)->first();

        if (!$plugin || !$plugin->is_active) {
            return false;
        }

        Plugin::where('slug', $slug)->update([
            'is_active' => false,
        ]);

        $this->activePlugins = array_values(
            array_filter(
                $this->activePlugins,
                fn(string $activeSlug): bool => $activeSlug !== $slug
            )
        );

        $this->events->trigger(
            "plugin.deactivated.{$slug}",
            [
                'reason' => 'invalid_manifest',
                'errors' => $errors,
            ]
        );

        return true;
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

    private function registerPluginAutoload(
        array $manifest,
        string $pluginPath
    ): void {
        $psr4 = $manifest['autoload']['psr-4'] ?? [];

        if (!is_array($psr4) || $psr4 === []) {
            return;
        }

        $loader = require dirname(__DIR__, 3) . '/vendor/autoload.php';

        foreach ($psr4 as $namespace => $relativePath) {
            $absolutePath = $pluginPath . DIRECTORY_SEPARATOR .
                trim($relativePath, '/\\');

            $loader->addPsr4($namespace, $absolutePath);
        }
    }

    private function createProvider(array $manifest, string $pluginPath, Router $router): PluginServiceProviderInterface
    {
        $providerClass = $manifest['provider'] ?? null;

        if (!is_string($providerClass) || $providerClass === '') {
            throw new RuntimeException('В plugin.json не е зададен provider клас.');
        }

        if (!class_exists($providerClass)) {
            throw new RuntimeException("Provider класът {$providerClass} не беше намерен.");
        }

        $provider = new $providerClass(
            $this->events,
            $router,
            $manifest,
            $pluginPath
        );

        if (!$provider instanceof PluginServiceProviderInterface) {
            throw new RuntimeException(
                "Provider класът {$providerClass} трябва да имплементира "
                . PluginServiceProviderInterface::class
                . '.'
            );
        }

        return $provider;
    }

    private function registerSinglePlugin(
        string $slug,
        Router $router
    ): ?PluginServiceProviderInterface {
        $manifest = $this->getManifest($slug);

        if (!($manifest['manifest_valid'] ?? false)) {
            $this->deactivateInvalidPlugin(
                $slug,
                $manifest['manifest_errors'] ?? []
            );

            return null;
        }

        try {
            $pluginPath = $this->pluginsPath .
                DIRECTORY_SEPARATOR . $slug;

            $this->registerPluginAutoload(
                $manifest,
                $pluginPath
            );

            $provider = $this->createProvider(
                $manifest,
                $pluginPath,
                $router
            );

            $provider->register();

            $this->providers[$slug] = $provider;

            return $provider;
        } catch (\Throwable $exception) {
            $this->deactivateInvalidPlugin(
                $slug,
                [
                    'Provider initialization failed: ' .
                    $exception->getMessage(),
                ]
            );

            $this->events->trigger(
                "plugin.failed.{$slug}",
                [
                    'stage' => 'register',
                    'exception' => $exception,
                ]
            );

            return null;
        }
    }

    public function migrateUpdatedPlugin(string $slug): bool
    {
        $plugin = Plugin::where('slug', $slug)->first();

        if (!$plugin || !(bool) $plugin->is_installed) {
            throw new RuntimeException(
                "Плъгинът „{$slug}“ не е инсталиран."
            );
        }

        $manifest = $this->getManifest($slug);

        if (!($manifest['manifest_valid'] ?? false)) {
            throw new RuntimeException(
                'Новият manifest не е валиден: '
                . implode(' ', $manifest['manifest_errors'] ?? [])
            );
        }

        $installedVersion = (string) $plugin->version;
        $availableVersion = (string) $manifest['version'];

        if (version_compare($availableVersion, $installedVersion, '<=')) {
            throw new RuntimeException(
                "Версия {$availableVersion} не е по-нова от инсталираната версия {$installedVersion}."
            );
        }

        $pluginPath = $this->getPluginDirectory($slug);

        $this->registerPluginAutoload(
            $manifest,
            $pluginPath
        );

        try {
            $this->runPluginMigrations(
                $slug,
                $manifest,
                $pluginPath
            );

            Plugin::where('slug', $slug)->update([
                'version' => $availableVersion,
            ]);

            $this->events->trigger(
                "plugin.updated.{$slug}",
                [
                    'slug' => $slug,
                    'from_version' => $installedVersion,
                    'to_version' => $availableVersion,
                ]
            );

            return true;
        } catch (\Throwable $exception) {
            $this->events->trigger(
                "plugin.failed.{$slug}",
                [
                    'stage' => 'update',
                    'exception' => $exception,
                ]
            );

            throw new RuntimeException(
                "Обновяването на плъгина „{$slug}“ беше неуспешно: "
                . $exception->getMessage(),
                previous: $exception
            );
        }
    }

    private function rollbackAllPluginMigrations(
        string $slug,
        string $pluginPath
    ): void {
        $migrationsPath = $pluginPath
            . DIRECTORY_SEPARATOR
            . 'database'
            . DIRECTORY_SEPARATOR
            . 'migrations';

        $tablePrefix = $this->makePluginTablePrefix($slug);

        while (
            $this->migrationManager
                ->rollback(
                    pluginSlug: $slug,
                    migrationsPath: $migrationsPath,
                    tablePrefix: $tablePrefix,
                )
                ->count() > 0
        ) {
            // Връща batch-овете един по един до пълно изчистване.
        }
    }
}