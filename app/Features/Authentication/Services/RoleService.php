<?php

declare(strict_types=1);

namespace Flex\Features\Authentication\Services;

use Flex\Features\Authentication\Models\Role;
use InvalidArgumentException;

final class RoleService
{
    public function save(?Role $role, array $input): Role
    {
        $name = trim((string) ($input['name'] ?? ''));
        $slug = strtolower(trim((string) ($input['slug'] ?? '')));
        $slug = preg_replace('/[^a-z0-9._-]+/', '-', $slug ?: $name) ?: '';
        if ($name === '' || $slug === '') { throw new InvalidArgumentException('Името и ключът на ролята са задължителни.'); }
        $exists = Role::withTrashed()->where('slug', $slug)->when($role, fn ($q) => $q->whereKeyNot($role->id))->exists();
        if ($exists) { throw new InvalidArgumentException('Вече има роля с този ключ.'); }
        $data = ['name' => $name, 'slug' => $slug, 'description' => trim((string) ($input['description'] ?? '')), 'is_active' => (bool) ($input['is_active'] ?? false), 'priority' => (int) ($input['priority'] ?? 0)];
        $role ? $role->update($data) : $role = Role::query()->create($data);
        $role->permissions()->sync(array_values(array_filter(array_map('intval', (array) ($input['permissions'] ?? [])))));
        return $role->load('permissions');
    }
}
