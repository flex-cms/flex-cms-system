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
    use PluginDiscoveryTrait, HandlesTableFilters, CrudHelper, PluginUpdatable;

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
    public function index()
    {
        $this->discoverAndSyncPlugins();

        $deactivatedPlugins = $this->pluginManager
            ->deactivateInvalidPlugins();

        $query = Plugin::query();

        $this->applySearch($query, ['name', 'slug']);

        if (!empty($_GET['status'])) {
            $this->applyStatusFilter($query, $_GET['status']);
        }

        $this->applySorting(
            $query,
            ['name', 'is_active', 'created_at'],
            'name',
            'asc'
        );

        $plugins = $query->get()->map(function (Plugin $plugin) {
            $plugin->setAttribute(
                'manifest',
                $this->pluginManager->getManifest($plugin->slug)
            );

            return $plugin;
        });

        $data = [
            'title' => 'Управление на плъгини',
            'plugins' => $plugins,
            'deactivatedPlugins' => $deactivatedPlugins,
        ];

        render_view('admin/plugins/index', $data);
    }

    #[UseExceptions]
    public function toggle()
    {
        $data = $this->getJsonInput();
        $id = $data['id'] ?? null;

        if (!$id) {
            return $this->jsonResponse(
                false,
                'Липсва ID на плъгина.'
            );
        }

        $plugin = Plugin::find($id);

        if (!$plugin) {
            return $this->jsonResponse(
                false,
                'Плъгинът не беше намерен.'
            );
        }

        $willActivate = !$plugin->is_active;

        if ($willActivate) {
            $manifest = $this->pluginManager
                ->getManifest($plugin->slug);

            if (!$manifest['manifest_valid']) {
                return $this->jsonResponse(
                    false,
                    'Плъгинът не може да бъде активиран: ' .
                    implode(' ', $manifest['manifest_errors'])
                );
            }
        }

        $plugin->is_active = $willActivate;
        $plugin->save();

        if ($plugin->is_active) {
            PluginManager::activate($plugin->slug);

            $this->pluginManager->initSinglePlugin(
                $plugin->slug
            );
        } else {
            $this->events->trigger(
                "plugin.deactivated.{$plugin->slug}"
            );
        }

        $statusText = $plugin->is_active
            ? 'активиран'
            : 'деактивиран';

        return $this->jsonResponse(
            true,
            "Плъгинът беше {$statusText} успешно!"
        );
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