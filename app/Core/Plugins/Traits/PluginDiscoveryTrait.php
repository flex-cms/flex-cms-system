<?php

namespace Flex\Core\Plugins\Traits;

use Flex\Models\Plugin;

trait PluginDiscoveryTrait
{
    protected function discoverAndSyncPlugins(): void
    {
        $pluginsPath = plugins_path();

        if (!is_dir($pluginsPath)) {
            return;
        }

        $folders = array_filter(glob($pluginsPath . '/*'), 'is_dir');

        foreach ($folders as $folder) {
            $slug = basename($folder);
            $manifest = $this->getManifestData($folder, $slug);

            $this->syncPluginRecord($slug, $manifest);
        }
    }

    protected function getManifestData(string $folder, string $slug): array
    {
        $manifestPath = $folder . '/plugin.json';

        $data = [
            'name' => ucfirst(str_replace('-', ' ', $slug)),
            'description' => 'Управление на функционалности за ' . $slug,
            'version' => '1.0.0',
            'author' => null,
            'author_url' => null,
            'requires' => null,
        ];

        if (file_exists($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $data['name'] = $manifest['name'] ?? $data['name'];
                $data['description'] = $manifest['description'] ?? $data['description'];
                $data['version'] = $manifest['version'] ?? $data['version'];
                $data['author'] = $manifest['author'] ?? null;
                $data['author_url'] = $manifest['author_url'] ?? null;
                $data['requires'] = $manifest['requires'] ?? null;
            }
        }

        return $data;
    }

    protected function syncPluginRecord(string $slug, array $manifest): void
    {
        $plugin = Plugin::where('slug', $slug)->first();

        if (!$plugin) {
            Plugin::create([
                'name' => $manifest['name'],
                'slug' => $slug,
                'description' => $manifest['description'],
                'author' => $manifest['author'],
                'author_url' => $manifest['author_url'],
                'requires' => $manifest['requires'],
                'is_active' => false,
                'is_installed' => false,
                'version' => null,
            ]);
        } else {
            $dbRequires = is_string($plugin->requires) ? json_decode($plugin->requires, true) : $plugin->requires;

            if (
                $plugin->name !== $manifest['name'] ||
                $plugin->description !== $manifest['description'] ||
                $plugin->author !== $manifest['author'] ||
                ($plugin->author_url ?? null) !== $manifest['author_url'] ||
                $dbRequires !== $manifest['requires']
            ) {
                Plugin::where('slug', $slug)->update([
                    'name' => $manifest['name'],
                    'description' => $manifest['description'],
                    'author' => $manifest['author'],
                    'author_url' => $manifest['author_url'],
                    'requires' => $manifest['requires']
                ]);
            }
        }
    }
}
