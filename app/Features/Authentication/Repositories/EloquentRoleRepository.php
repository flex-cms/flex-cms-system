<?php

declare(strict_types=1);

namespace Flex\Features\Authentication\Repositories;

use Flex\Features\Authentication\Models\Role;

final class EloquentRoleRepository implements RoleRepositoryInterface
{
    public function all(): iterable { return Role::query()->with('permissions')->orderBy('priority')->orderBy('name')->get(); }
    public function find(int $id): ?Role { return Role::query()->with('permissions')->find($id); }
}
