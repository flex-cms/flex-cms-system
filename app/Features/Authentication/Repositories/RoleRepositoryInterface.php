<?php

declare(strict_types=1);

namespace Flex\Features\Authentication\Repositories;

use Flex\Features\Authentication\Models\Role;
use Illuminate\Database\Eloquent\Collection;

interface RoleRepositoryInterface
{
    public function all(): Collection;

    public function find(int $id): ?Role;

    public function findOrFail(int $id): Role;

    public function paginate(
        int $page,
        int $perPage,
        ?string $sortBy = null,
        ?string $sortDirection = null,
        ?string $search = null,
        array $filters = []
    ): array;

    public function create(array $data): Role;

    public function update(Role $role, array $data): Role;

    public function delete(Role $role): void;

    public function restore(Role $role): void;

    public function forceDelete(Role $role): void;

    public function transaction(callable $callback): mixed;

    public function bulkSetActive(array $ids, bool $active): int;

    public function bulkDelete(array $ids): int;

    public function bulkRestore(array $ids): int;

    public function bulkForceDelete(array $ids): int;
}
