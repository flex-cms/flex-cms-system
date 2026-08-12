<?php

declare(strict_types=1);

namespace Flex\Features\Authentication\Repositories;

use Flex\Features\Authentication\Models\Role;
use Illuminate\Database\Eloquent\Collection;

final class EloquentRoleRepository implements RoleRepositoryInterface
{
    private const SORTABLE_COLUMNS = [
        'id',
        'name',
        'slug',
        'priority',
        'is_active',
        'created_at',
        'updated_at',
    ];

    public function all(): Collection
    {
        return Role::query()
            ->with('permissions')
            ->withCount('users')
            ->orderBy('priority')
            ->orderBy('name')
            ->get();
    }

    public function find(int $id): ?Role
    {
        return Role::query()
            ->withTrashed()
            ->with('permissions')
            ->withCount('users')
            ->find($id);
    }

    public function findOrFail(int $id): Role
    {
        return Role::query()
            ->withTrashed()
            ->with('permissions')
            ->withCount('users')
            ->findOrFail($id);
    }

    public function paginate(
        int $page,
        int $perPage,
        ?string $sortBy = null,
        ?string $sortDirection = null,
        ?string $search = null,
        array $filters = []
    ): array {
        $page = max(1, $page);
        $perPage = max(1, min(250, $perPage));

        $sortBy = in_array($sortBy, self::SORTABLE_COLUMNS, true)
            ? $sortBy
            : 'priority';

        $sortDirection = $sortDirection === 'desc'
            ? 'desc'
            : 'asc';

        $query = Role::query()
            ->with('permissions')
            ->withCount('users');

        $search = trim((string) $search);

        if ($search !== '') {
            $query->where(
                static function ($query) use ($search): void {
                    $query
                        ->where('name', 'LIKE', '%' . $search . '%')
                        ->orWhere('slug', 'LIKE', '%' . $search . '%')
                        ->orWhere('description', 'LIKE', '%' . $search . '%')
                        ->orWhereHas(
                            'permissions',
                            static fn($query) => $query
                                ->where('name', 'LIKE', '%' . $search . '%')
                                ->orWhere('slug', 'LIKE', '%' . $search . '%')
                        );
                }
            );
        }

        $status = $filters['status'] ?? null;

        if ($status === 'deleted') {
            $query->onlyTrashed();
        } elseif ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        $total = (clone $query)->count();
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min($page, $lastPage);

        $rows = $query
            ->orderBy($sortBy, $sortDirection)
            ->orderBy('id')
            ->forPage($page, $perPage)
            ->get();

        return [
            'data' => $rows
                ->map(
                    static fn(Role $role): array => [
                        'id' => (int) $role->id,
                        'name' => $role->name,
                        'slug' => $role->slug,
                        'description' => $role->description,
                        'priority' => (int) $role->priority,
                        'color' => $role->color,
                        'is_active' => (bool) $role->is_active,
                        'is_default' => (bool) $role->is_default,
                        'permissions_count' => $role->permissions->count(),
                        'users_count' => (int) $role->users_count,
                        'created_at' => $role->created_at?->toAtomString(),
                        'updated_at' => $role->updated_at?->toAtomString(),
                        'deleted_at' => $role->deleted_at?->toAtomString(),
                    ]
                )
                ->values()
                ->all(),
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => $lastPage,
            ],
        ];
    }

    public function create(array $data): Role
    {
        return Role::query()->create($data);
    }

    public function update(Role $role, array $data): Role
    {
        $role->fill($data);
        $role->save();

        return $role->refresh();
    }

    public function delete(Role $role): void
    {
        $role->delete();
    }

    public function restore(Role $role): void
    {
        $role->restore();
    }

    public function forceDelete(Role $role): void
    {
        $role->forceDelete();
    }

    public function transaction(callable $callback): mixed
    {
        return (new Role())->getConnection()->transaction($callback);
    }

    public function bulkSetActive(array $ids, bool $active): int
    {
        return Role::query()
            ->whereIn('id', $ids)
            ->update(['is_active' => $active]);
    }

    public function bulkDelete(array $ids): int
    {
        return $this->applyToMany(
            Role::query()->whereIn('id', $ids)->get(),
            static fn(Role $role): bool => (bool) $role->delete()
        );
    }

    public function bulkRestore(array $ids): int
    {
        return $this->applyToMany(
            Role::query()->onlyTrashed()->whereIn('id', $ids)->get(),
            static fn(Role $role): bool => (bool) $role->restore()
        );
    }

    public function bulkForceDelete(array $ids): int
    {
        return $this->applyToMany(
            Role::query()->onlyTrashed()->whereIn('id', $ids)->get(),
            static fn(Role $role): bool => (bool) $role->forceDelete()
        );
    }

    private function applyToMany(
        Collection $roles,
        callable $action
    ): int {
        $affected = 0;

        foreach ($roles as $role) {
            if ($action($role)) {
                $affected++;
            }
        }

        return $affected;
    }
}
