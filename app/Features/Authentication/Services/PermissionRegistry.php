<?php

declare(strict_types=1);

namespace Flex\Features\Authentication\Services;

use Flex\Features\Authentication\Models\Permission;

final class PermissionRegistry
{
    public function sync(): void
    {
        $path = dirname(__DIR__) . '/Resources/permissions.json';
        $definitions = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        foreach ($definitions['permissions'] ?? [] as $slug => $data) {
            Permission::withTrashed()->updateOrCreate(['slug' => $slug], [
                'name' => $data['name'], 'module' => $data['module'], 'description' => $data['description'] ?? null,
                'is_active' => true, 'deleted_at' => null,
            ]);
        }
    }
}
