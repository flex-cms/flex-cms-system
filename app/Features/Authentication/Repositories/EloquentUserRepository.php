<?php

declare(strict_types=1);

namespace Flex\Features\Authentication\Repositories;

use Flex\Features\Authentication\Models\User;
use Illuminate\Database\Eloquent\Collection;

final class EloquentUserRepository implements UserRepositoryInterface
{
    private const SORTABLE_COLUMNS = [
        'id',
        'fullname',
        'email',
        'is_active',
        'is_super_admin',
        'created_at',
        'updated_at',
    ];

    public function all(): Collection
    {
        return User::query()
            ->with('roles')
            ->orderBy('fullname')
            ->get();
    }

    public function find(int $id): ?User
    {
        return User::query()
            ->withTrashed()
            ->with('roles')
            ->find($id);
    }

    public function findOrFail(int $id): User
    {
        return User::query()
            ->withTrashed()
            ->with('roles')
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

        $sortBy = in_array(
            $sortBy,
            self::SORTABLE_COLUMNS,
            true
        )
            ? $sortBy
            : 'fullname';

        $sortDirection = $sortDirection === 'desc'
            ? 'desc'
            : 'asc';

        $query = User::query()
            ->with('roles');

        $search = trim((string) $search);

        if ($search !== '') {
            $query->where(
                static function ($query) use ($search): void {
                    $query
                        ->where(
                            'fullname',
                            'LIKE',
                            '%' . $search . '%'
                        )
                        ->orWhere(
                            'email',
                            'LIKE',
                            '%' . $search . '%'
                        )
                        ->orWhereHas(
                            'roles',
                            static function ($query) use ($search): void {
                                $query
                                    ->where(
                                        'name',
                                        'LIKE',
                                        '%' . $search . '%'
                                    )
                                    ->orWhere(
                                        'slug',
                                        'LIKE',
                                        '%' . $search . '%'
                                    );
                            }
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
        } elseif ($status === 'super-admin') {
            $query->where('is_super_admin', true);
        }

        $total = (clone $query)->count();

        $lastPage = max(
            1,
            (int) ceil($total / $perPage)
        );

        $page = min(
            $page,
            $lastPage
        );

        $rows = $query
            ->orderBy($sortBy, $sortDirection)
            ->orderBy('id')
            ->forPage($page, $perPage)
            ->get();

        $data = $rows
            ->map(
                static fn(User $user): array => [
                    'id' => (int) $user->id,
                    'fullname' => $user->fullname ?: 'Без име',
                    'email' => $user->email,
                    'roles' => $user->roles
                        ->map(
                            static fn($role): array => [
                                'id' => (int) $role->id,
                                'name' => $role->name,
                                'slug' => $role->slug,
                                'color' => $role->color,
                            ]
                        )
                        ->values()
                        ->all(),
                    'role_names' => $user->roles
                        ->pluck('name')
                        ->implode(', '),
                    'is_active' => (bool) $user->is_active,
                    'is_super_admin' => (bool) $user->is_super_admin,
                    'last_login' => $user->last_login?->toAtomString(),
                    'created_at' => $user->created_at?->toAtomString(),
                    'updated_at' => $user->updated_at?->toAtomString(),
                    'deleted_at' => $user->deleted_at?->toAtomString(),
                ]
            )
            ->values()
            ->all();

        return [
            'data' => $data,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => $lastPage,
            ],
        ];
    }

    public function create(array $data): User
    {
        return User::query()->create($data);
    }

    public function update(
        User $user,
        array $data
    ): User {
        $user->fill($data);
        $user->save();

        return $user->refresh();
    }

    public function delete(User $user): void
    {
        $user->delete();
    }

    public function restore(User $user): void
    {
        $user->restore();
    }

    public function forceDelete(User $user): void
    {
        $user->forceDelete();
    }

    public function transaction(callable $callback): mixed
    {
        return (new User())
            ->getConnection()
            ->transaction($callback);
    }

    public function bulkSetActive(
        array $ids,
        bool $active
    ): int {
        return User::query()
            ->whereIn('id', $ids)
            ->update([
                'is_active' => $active,
            ]);
    }

    public function bulkDelete(array $ids): int
    {
        $users = User::query()
            ->whereIn('id', $ids)
            ->get();

        $affected = 0;

        foreach ($users as $user) {
            if ($user->delete()) {
                $affected++;
            }
        }

        return $affected;
    }

    public function bulkRestore(array $ids): int
    {
        $users = User::query()
            ->onlyTrashed()
            ->whereIn('id', $ids)
            ->get();

        $affected = 0;

        foreach ($users as $user) {
            if ($user->restore()) {
                $affected++;
            }
        }

        return $affected;
    }

    public function bulkForceDelete(array $ids): int
    {
        $users = User::query()
            ->onlyTrashed()
            ->whereIn('id', $ids)
            ->get();

        $affected = 0;

        foreach ($users as $user) {
            if ($user->forceDelete()) {
                $affected++;
            }
        }

        return $affected;
    }
}
