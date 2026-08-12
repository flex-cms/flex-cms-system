<?php

declare(strict_types=1);

namespace Flex\Features\Authentication\Services;

use Flex\Features\Authentication\Models\Permission;
use Flex\Features\Authentication\Models\Role;
use Flex\Features\Authentication\Repositories\RoleRepositoryInterface;
use InvalidArgumentException;

final readonly class RoleService
{
    public function __construct(
        private RoleRepositoryInterface $roles
    ) {
    }

    public function paginate(array $input): array
    {
        $page = max(1, (int) ($input['page'] ?? 1));
        $perPage = (int) ($input['per_page'] ?? 25);

        if (!in_array($perPage, [10, 20, 25, 50, 100], true)) {
            $perPage = 25;
        }

        $direction = isset($input['direction'])
            ? strtolower(trim((string) $input['direction']))
            : null;

        if ($direction !== 'asc' && $direction !== 'desc') {
            $direction = null;
        }

        return $this->roles->paginate(
            page: $page,
            perPage: $perPage,
            sortBy: isset($input['sort'])
                ? trim((string) $input['sort'])
                : null,
            sortDirection: $direction,
            search: isset($input['search'])
                ? trim((string) $input['search'])
                : null,
            filters: isset($input['filter']) && is_array($input['filter'])
                ? $input['filter']
                : []
        );
    }

    public function findOrFail(int $id): Role
    {
        return $this->roles->findOrFail($id);
    }

    public function create(array $input): Role
    {
        $data = $this->normalize($input);
        $permissionIds = $this->normalizePermissionIds(
            $input['permissions'] ?? []
        );

        return $this->roles->transaction(
            function () use ($data, $permissionIds): Role {
                $role = $this->roles->create($data);
                $role->permissions()->sync($permissionIds);

                return $role->load('permissions');
            }
        );
    }

    public function update(int $id, array $input): Role
    {
        $role = $this->findOrFail($id);

        if ($role->trashed()) {
            throw new InvalidArgumentException(
                'Изтрита роля не може да бъде редактирана.'
            );
        }

        $data = $this->normalize($input, $role);
        $permissionIds = $this->normalizePermissionIds(
            $input['permissions'] ?? []
        );

        return $this->roles->transaction(
            function () use ($role, $data, $permissionIds): Role {
                $saved = $this->roles->update($role, $data);
                $saved->permissions()->sync($permissionIds);

                return $saved->load('permissions');
            }
        );
    }

    public function toggle(int $id): Role
    {
        $role = $this->findOrFail($id);

        if ($role->trashed()) {
            throw new InvalidArgumentException(
                'Статусът на изтрита роля не може да бъде променян.'
            );
        }

        return $this->roles->update(
            $role,
            ['is_active' => !$role->is_active]
        );
    }

    public function delete(int $id): void
    {
        $role = $this->findOrFail($id);
        $this->assertDeletable($role);
        $this->roles->delete($role);
    }

    public function restore(int $id): void
    {
        $role = $this->findOrFail($id);

        if ($role->trashed()) {
            $this->roles->restore($role);
        }
    }

    public function forceDelete(int $id): void
    {
        $role = $this->findOrFail($id);
        $this->assertDeletable($role);

        if (!$role->trashed()) {
            throw new InvalidArgumentException(
                'Ролята трябва първо да бъде преместена в кошчето.'
            );
        }

        $this->roles->forceDelete($role);
    }

    public function bulk(array $input): array
    {
        $action = trim((string) ($input['action'] ?? ''));
        $ids = $this->normalizeIds($input['ids'] ?? []);

        if ($ids === []) {
            throw new InvalidArgumentException(
                'Не са избрани валидни роли.'
            );
        }

        $this->assertBulkActionAllowed($action, $ids);

        $affected = match ($action) {
            'activate' => $this->roles->bulkSetActive($ids, true),
            'deactivate' => $this->roles->bulkSetActive($ids, false),
            'trash' => $this->roles->bulkDelete($ids),
            'restore' => $this->roles->bulkRestore($ids),
            'force-delete' => $this->roles->bulkForceDelete($ids),
            default => throw new InvalidArgumentException(
                'Невалидно групово действие.'
            ),
        };

        $message = match ($action) {
            'activate' => sprintf('Активирани роли: %d.', $affected),
            'deactivate' => sprintf('Деактивирани роли: %d.', $affected),
            'trash' => sprintf('Преместени в кошчето роли: %d.', $affected),
            'restore' => sprintf('Възстановени роли: %d.', $affected),
            'force-delete' => sprintf('Изтрити завинаги роли: %d.', $affected),
        };

        return ['affected' => $affected, 'message' => $message];
    }

    private function normalize(array $input, ?Role $role = null): array
    {
        $name = trim((string) ($input['name'] ?? ''));

        if ($name === '') {
            throw new InvalidArgumentException(
                'Името на ролята е задължително.'
            );
        }

        $slug = strtolower(trim((string) ($input['slug'] ?? '')));
        $slug = preg_replace('/[^a-z0-9._-]+/', '-', $slug) ?: '';
        $slug = trim($slug, '-');

        if ($slug === '') {
            throw new InvalidArgumentException(
                'Ключът на ролята е задължителен и трябва да съдържа латински букви, цифри, точка, тире или долна черта.'
            );
        }

        $exists = Role::query()
            ->withTrashed()
            ->where('slug', $slug)
            ->when(
                $role !== null,
                static fn($query) => $query->whereKeyNot($role->id)
            )
            ->exists();

        if ($exists) {
            throw new InvalidArgumentException(
                'Вече има роля с този ключ, включително в кошчето.'
            );
        }

        $color = trim((string) ($input['color'] ?? '#6366f1'));

        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
            throw new InvalidArgumentException(
                'Цветът трябва да бъде във формат #RRGGBB.'
            );
        }

        return [
            'name' => $name,
            'slug' => $slug,
            'description' => $this->nullableString(
                $input['description'] ?? null
            ),
            'priority' => (int) ($input['priority'] ?? 0),
            'color' => strtolower($color),
            'is_active' => $this->toBoolean(
                $input['is_active'] ?? false
            ),
            // TODO: Добавете управление на is_default, когато бъде
            // уточнено автоматичното задаване на роля при регистрация.
        ];
    }

    private function normalizePermissionIds(mixed $permissions): array
    {
        if (!is_array($permissions)) {
            return [];
        }

        $ids = array_is_list($permissions)
            ? array_map('intval', $permissions)
            : array_map(
                'intval',
                array_keys(
                    array_filter(
                        $permissions,
                        fn(mixed $value): bool => $this->toBoolean($value)
                    )
                )
            );

        $ids = array_values(array_unique(array_filter(
            $ids,
            static fn(int $id): bool => $id > 0
        )));

        if ($ids === []) {
            return [];
        }

        $validIds = Permission::query()
            ->where('is_active', true)
            ->whereIn('id', $ids)
            ->pluck('id')
            ->map(static fn($id): int => (int) $id)
            ->values()
            ->all();

        if (count($validIds) !== count($ids)) {
            throw new InvalidArgumentException(
                'Избрано е невалидно или неактивно разрешение.'
            );
        }

        return $validIds;
    }

    private function assertDeletable(Role $role): void
    {
        if ($role->users()->exists()) {
            throw new InvalidArgumentException(
                'Ролята се използва от потребители и не може да бъде изтрита.'
            );
        }
    }

    private function assertBulkActionAllowed(string $action, array $ids): void
    {
        if (!in_array($action, [
            'activate', 'deactivate', 'trash', 'restore', 'force-delete',
        ], true)) {
            throw new InvalidArgumentException('Невалидно групово действие.');
        }

        $selected = Role::query()
            ->withTrashed()
            ->withCount('users')
            ->whereIn('id', $ids)
            ->get();

        if ($selected->count() !== count($ids)) {
            throw new InvalidArgumentException(
                'Една или повече роли не съществуват.'
            );
        }

        if (
            in_array($action, ['trash', 'force-delete'], true)
            && $selected->contains(
                static fn(Role $role): bool => (int) $role->users_count > 0
            )
        ) {
            throw new InvalidArgumentException(
                'Избраните роли включват роля, която се използва от потребители.'
            );
        }
    }

    private function normalizeIds(mixed $ids): array
    {
        return array_values(array_unique(array_filter(
            array_map(
                'intval',
                is_array($ids) ? $ids : []
            ),
            static fn(int $id): bool => $id > 0
        )));
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }

    private function toBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower((string) $value), [
            '1', 'true', 'yes', 'on',
        ], true);
    }
}
