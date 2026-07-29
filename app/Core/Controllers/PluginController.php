<?php

namespace Flex\Core\Controllers;

use Flex\Attributes\UseExceptions;
use Flex\Core\Events\EventManager;
use Flex\Core\Plugins\PluginManager;
use Flex\Core\Plugins\Traits\PluginDiscoveryTrait;
use Flex\Core\Plugins\Traits\PluginUpdatable;
use Flex\Core\Traits\CrudHelper;
use Flex\Core\Traits\HandlesTableFilters;
use Flex\Models\Plugin;

class PluginController extends BaseController
{
    use PluginDiscoveryTrait;
    use HandlesTableFilters;
    use CrudHelper;
    use PluginUpdatable;

    protected EventManager $events;
    protected PluginManager $pluginManager;

    public function __construct(
        EventManager $events,
        PluginManager $pluginManager
    ) {
        $this->events = $events;
        $this->pluginManager = $pluginManager;
    }

    #[UseExceptions]
    public function index(): void
    {
        $this->discoverAndSyncPlugins();

        $deactivatedPlugins = $this->pluginManager
            ->deactivateInvalidPlugins();

        $query = Plugin::query();

        $this->applySearch(
            $query,
            ['name', 'slug']
        );

        if (!empty($_GET['status'])) {
            $this->applyStatusFilter(
                $query,
                $_GET['status']
            );
        }

        $this->applySorting(
            $query,
            ['name', 'is_active', 'created_at'],
            'name',
            'asc'
        );

        $plugins = $query
            ->get()
            ->map(function (Plugin $plugin): Plugin {
                $plugin->setAttribute(
                    'manifest',
                    $this->pluginManager->getManifest(
                        $plugin->slug
                    )
                );

                return $plugin;
            });

        render_view('admin/plugins/index', [
            'title' => 'Управление на плъгини',
            'plugins' => $plugins,
            'deactivatedPlugins' => $deactivatedPlugins,
        ]);
    }

    #[UseExceptions]
    public function install()
    {
        $data = $this->getJsonInput();

        $slug = trim(
            (string) ($data['slug'] ?? '')
        );

        if ($slug === '') {
            return $this->jsonResponse(
                false,
                'Липсва slug на плъгина.'
            );
        }

        $this->pluginManager->install($slug);

        return $this->jsonResponse(
            true,
            'Плъгинът беше инсталиран успешно!'
        );
    }

    #[UseExceptions]
    public function toggle()
    {
        $data = $this->getJsonInput();

        $id = filter_var(
            $data['id'] ?? null,
            FILTER_VALIDATE_INT
        );

        if (!$id) {
            return $this->jsonResponse(
                false,
                'Липсва или е невалидно ID на плъгина.'
            );
        }

        $plugin = Plugin::find($id);

        if (!$plugin) {
            return $this->jsonResponse(
                false,
                'Плъгинът не беше намерен.'
            );
        }

        if ((bool) $plugin->is_active) {
            $this->pluginManager->deactivate(
                $plugin->slug
            );

            return $this->jsonResponse(
                true,
                'Плъгинът беше деактивиран успешно!',
                [
                    'id' => $plugin->id,
                    'slug' => $plugin->slug,
                    'is_active' => false,
                ]
            );
        }

        $this->pluginManager->activate(
            $plugin->slug
        );

        return $this->jsonResponse(
            true,
            'Плъгинът беше активиран успешно!',
            [
                'id' => $plugin->id,
                'slug' => $plugin->slug,
                'is_active' => true,
            ]
        );
    }

    #[UseExceptions]
    public function activate()
    {
        $plugin = $this->resolvePluginFromRequest();

        if (!$plugin) {
            return $this->jsonResponse(
                false,
                'Плъгинът не беше намерен.'
            );
        }

        $this->pluginManager->activate(
            $plugin->slug
        );

        return $this->jsonResponse(
            true,
            'Плъгинът беше активиран успешно!',
            [
                'id' => $plugin->id,
                'slug' => $plugin->slug,
                'is_active' => true,
            ]
        );
    }

    #[UseExceptions]
    public function deactivate()
    {
        $plugin = $this->resolvePluginFromRequest();

        if (!$plugin) {
            return $this->jsonResponse(
                false,
                'Плъгинът не беше намерен.'
            );
        }

        $this->pluginManager->deactivate(
            $plugin->slug
        );

        return $this->jsonResponse(
            true,
            'Плъгинът беше деактивиран успешно!',
            [
                'id' => $plugin->id,
                'slug' => $plugin->slug,
                'is_active' => false,
            ]
        );
    }

    #[UseExceptions]
    public function uninstall()
    {
        $data = $this->getJsonInput();

        $id = filter_var(
            $data['id'] ?? null,
            FILTER_VALIDATE_INT
        );

        $removeData = filter_var(
            $data['removeData']
            ?? $data['dropTables']
            ?? false,
            FILTER_VALIDATE_BOOLEAN
        );

        if (!$id) {
            return $this->jsonResponse(
                false,
                'Липсва или е невалидно ID на плъгина.'
            );
        }

        $plugin = Plugin::find($id);

        if (!$plugin) {
            return $this->jsonResponse(
                false,
                'Плъгинът вече е деинсталиран или не съществува.'
            );
        }

        $slug = $plugin->slug;

        $this->pluginManager->uninstall(
            $slug,
            $removeData
        );

        return $this->jsonResponse(
            true,
            'Плъгинът беше деинсталиран успешно!',
            [
                'slug' => $slug,
                'data_removed' => $removeData,
            ]
        );
    }

    #[UseExceptions]
    public function delete()
    {
        $data = $this->getJsonInput();

        $slug = trim(
            (string) ($data['slug'] ?? '')
        );

        if ($slug === '') {
            return $this->jsonResponse(
                false,
                'Липсва slug на плъгина.'
            );
        }

        $installedPlugin = Plugin::where(
            'slug',
            $slug
        )->first();

        if ($installedPlugin) {
            return $this->jsonResponse(
                false,
                'Плъгинът трябва първо да бъде деинсталиран.'
            );
        }

        $this->pluginManager->deletePluginFiles(
            $slug
        );

        return $this->jsonResponse(
            true,
            'Файловете на плъгина бяха изтрити успешно!'
        );
    }

    protected function resolvePluginFromRequest(): ?Plugin
    {
        $data = $this->getJsonInput();

        $id = filter_var(
            $data['id'] ?? null,
            FILTER_VALIDATE_INT
        );

        if (!$id) {
            return null;
        }

        return Plugin::find($id);
    }
}
