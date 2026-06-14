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
use Flex\Core\Routing\View;

class PluginController extends BaseController
{
    use PluginDiscoveryTrait, HandlesTableFilters, CrudHelper, PluginUpdatable;

    protected EventManager $events;
    protected PluginManager $pluginManager;

    public function __construct(EventManager $events, PluginManager $pluginManager)
    {
        $this->events = $events;
        $this->pluginManager = $pluginManager;
    }

    #[UseExceptions]
    public function index()
    {
        $this->discoverAndSyncPlugins();

        $query = Plugin::query();

        $this->applySearch($query, ['name', 'slug']);

        if (!empty($_GET['status'])) {
            $this->applyStatusFilter($query, $_GET['status']);
        }

        $this->applySorting($query, ['name', 'is_active'], 'name', 'asc');

        $plugins = $query->get();

        return $this->render(View::make('admin/plugins/index', [
            'title' => 'Управление на Плъгини',
            'plugins' => $plugins
        ], 'admin'));
    }

    #[UseExceptions]
    public function toggle()
    {
        $result = $this->toggleStatus(Plugin::class, 'is_active');

        if (!$result['success']) {
            return $this->jsonResponse(false, $result['message']);
        }

        $data = $this->getJsonInput();
        $id = $data['id'] ?? null;
        $plugin = Plugin::find($id);

        if ($plugin->is_active) {
            PluginManager::activate($plugin->slug);
        }

        $this->pluginManager->initSinglePlugin($plugin->slug);

        $plugin->is_active
            ? $this->events->trigger("plugin.activated.{$plugin->slug}")
            : $this->events->trigger("plugin.deactivated.{$plugin->slug}");

        $statusText = $plugin->is_active ? 'активиран' : 'деактивиран';
        return $this->jsonResponse(true, "Плъгинът беше {$statusText} успешно!");
    }

    #[UseExceptions]
    public function delete()
    {
        $data = $this->getJsonInput();
        $id = $data['id'] ?? null;
        $dropTables = (bool) ($data['dropTables'] ?? false);

        if (!$id) {
            return $this->jsonResponse(false, 'Невалидно ID за изтриване.');
        }

        $plugin = Plugin::find($id);
        if (!$plugin) {
            return $this->jsonResponse(false, 'Плъгинът вече е премахнат или не съществува.');
        }

        $this->pluginManager->deletePlugin($plugin->slug, $dropTables);

        $plugin->delete();

        return $this->jsonResponse(true, "Плъгинът беше премахнат успешно от системата.");
    }
}