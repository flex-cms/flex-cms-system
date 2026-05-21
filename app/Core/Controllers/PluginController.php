<?php

namespace Flex\Core\Controllers;

use Flex\Models\Plugin;
use Flex\Core\Routing\View;

class PluginController extends BaseController
{
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
                $requires = $manifest['requires'] ?? null;

                if (!$plugin) {
                    Plugin::create([
                        'name' => $name,
                        'slug' => $slug,
                        'description' => $description,
                        'author' => $author,
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
                        $dbRequires !== $requires
                    ) {
                        Plugin::where('slug', $slug)->update([
                            'name' => $name,
                            'description' => $description,
                            'version' => $version,
                            'author' => $author,
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

        $plugin->delete();

        return $this->jsonResponse(true, "Плъгинът беше премахнат успешно от системата.");
    }

    private function jsonResponse(bool $success, string $message)
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => $success, 'message' => $message]);
        exit;
    }
}
