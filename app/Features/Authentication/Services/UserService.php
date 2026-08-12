<?php

declare(strict_types=1);

namespace Flex\Features\Authentication\Services;

use Flex\Features\Authentication\Exceptions\SuperAdministratorAlreadyExistsException;
use Flex\Features\Authentication\Models\Role;
use Flex\Features\Authentication\Models\User;
use Flex\Features\Authentication\Repositories\UserRepositoryInterface;
use InvalidArgumentException;

final readonly class UserService
{
    public function __construct(
        private UserRepositoryInterface $users
    ) {
    }

    public function paginate(
        array $input,
        ?User $actor = null
    ): array {
        $page = max(
            1,
            (int) ($input['page'] ?? 1)
        );

        $perPage = (int) ($input['per_page'] ?? 25);

        if (!in_array($perPage, [10, 20, 25, 50, 100], true)) {
            $perPage = 25;
        }

        $sortBy = isset($input['sort'])
            ? trim((string) $input['sort'])
            : null;

        $sortDirection = isset($input['direction'])
            ? strtolower(trim((string) $input['direction']))
            : null;

        if (
            $sortDirection !== 'asc'
            && $sortDirection !== 'desc'
        ) {
            $sortDirection = null;
        }

        $search = isset($input['search'])
            ? trim((string) $input['search'])
            : null;

        $filters = isset($input['filter'])
            && is_array($input['filter'])
            ? $input['filter']
            : [];

        $result = $this->users->paginate(
            page: $page,
            perPage: $perPage,
            sortBy: $sortBy,
            sortDirection: $sortDirection,
            search: $search,
            filters: $filters
        );

        foreach ($result['data'] as &$row) {
            $row['is_current_user'] = $actor !== null
                && (int) $row['id'] === (int) $actor->id;
        }

        unset($row);

        return $result;
    }

    public function findOrFail(int $id): User
    {
        return $this->users->findOrFail($id);
    }

    public function create(array $input): User
    {
        $data = $this->normalize($input);
        $roleIds = $this->normalizeRoleIds(
            $input['roles'] ?? []
        );

        return $this->users->transaction(
            function () use ($data, $roleIds): User {
                $user = $this->users->create($data);

                $user->roles()->sync(
                    $user->is_super_admin
                        ? []
                        : $roleIds
                );

                return $user->load('roles');
            }
        );
    }

    public function update(
        int $id,
        array $input,
        ?User $actor = null
    ): User {
        $user = $this->findOrFail($id);

        if ($user->trashed()) {
            throw new InvalidArgumentException(
                'Изтрит потребител не може да бъде редактиран.'
            );
        }

        $data = $this->normalize(
            $input,
            $user
        );

        if (
            $actor !== null
            && (int) $actor->id === (int) $user->id
            && !$data['is_active']
        ) {
            throw new InvalidArgumentException(
                'Не можете да деактивирате собствения си профил.'
            );
        }

        $roleIds = $this->normalizeRoleIds(
            $input['roles'] ?? []
        );

        return $this->users->transaction(
            function () use ($user, $data, $roleIds): User {
                $saved = $this->users->update(
                    $user,
                    $data
                );

                $saved->roles()->sync(
                    $saved->is_super_admin
                        ? []
                        : $roleIds
                );

                return $saved->load('roles');
            }
        );
    }

    public function delete(
        int $id,
        ?User $actor
    ): void {
        $user = $this->findOrFail($id);

        $this->assertDeletable(
            $user,
            $actor
        );

        $this->users->delete($user);
    }

    public function restore(int $id): void
    {
        $user = $this->findOrFail($id);

        if (!$user->trashed()) {
            return;
        }

        $this->users->restore($user);
    }

    public function forceDelete(
        int $id,
        ?User $actor
    ): void {
        $user = $this->findOrFail($id);

        $this->assertDeletable(
            $user,
            $actor
        );

        if (!$user->trashed()) {
            throw new InvalidArgumentException(
                'Потребителят трябва първо да бъде преместен в кошчето.'
            );
        }

        $this->users->forceDelete($user);
    }

    public function toggle(
        int $id,
        ?User $actor
    ): User {
        $user = $this->findOrFail($id);

        if ($user->trashed()) {
            throw new InvalidArgumentException(
                'Статусът на изтрит потребител не може да бъде променян.'
            );
        }

        if (
            $actor !== null
            && (int) $actor->id === (int) $user->id
            && $user->is_active
        ) {
            throw new InvalidArgumentException(
                'Не можете да деактивирате собствения си профил.'
            );
        }

        return $this->users->update(
            $user,
            [
                'is_active' => !$user->is_active,
            ]
        );
    }

    public function bulk(
        array $input,
        ?User $actor
    ): array {
        $action = trim(
            (string) ($input['action'] ?? '')
        );

        $ids = $this->normalizeIds(
            $input['ids'] ?? []
        );

        if ($ids === []) {
            throw new InvalidArgumentException(
                'Не са избрани валидни потребители.'
            );
        }

        $this->assertBulkActionAllowed(
            $action,
            $ids,
            $actor
        );

        $affected = match ($action) {
            'activate' => $this->users->bulkSetActive(
                $ids,
                true
            ),

            'deactivate' => $this->users->bulkSetActive(
                $ids,
                false
            ),

            'trash' => $this->users->bulkDelete(
                $ids
            ),

            'restore' => $this->users->bulkRestore(
                $ids
            ),

            'force-delete' => $this->users->bulkForceDelete(
                $ids
            ),

            default => throw new InvalidArgumentException(
                'Невалидно групово действие.'
            ),
        };

        $message = match ($action) {
            'activate' => sprintf(
                'Активирани потребители: %d.',
                $affected
            ),

            'deactivate' => sprintf(
                'Деактивирани потребители: %d.',
                $affected
            ),

            'trash' => sprintf(
                'Преместени в кошчето потребители: %d.',
                $affected
            ),

            'restore' => sprintf(
                'Възстановени потребители: %d.',
                $affected
            ),

            'force-delete' => sprintf(
                'Изтрити завинаги потребители: %d.',
                $affected
            ),
        };

        return [
            'affected' => $affected,
            'message' => $message,
        ];
    }

    private function normalize(
        array $input,
        ?User $user = null
    ): array {
        $fullname = trim(
            (string) ($input['fullname'] ?? '')
        );

        if ($fullname === '') {
            throw new InvalidArgumentException(
                'Името на потребителя е задължително.'
            );
        }

        if (mb_strlen($fullname) > 100) {
            throw new InvalidArgumentException(
                'Името не може да бъде по-дълго от 100 символа.'
            );
        }

        $email = strtolower(
            trim(
                (string) ($input['email'] ?? '')
            )
        );

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException(
                'Невалиден имейл адрес.'
            );
        }

        $duplicate = User::query()
            ->withTrashed()
            ->where('email', $email)
            ->when(
                $user !== null,
                static fn($query) => $query->whereKeyNot($user->id)
            )
            ->exists();

        if ($duplicate) {
            throw new InvalidArgumentException(
                'Вече има потребител с този имейл, включително в кошчето.'
            );
        }

        $isSuperAdministrator = $this->toBoolean(
            $input['is_super_admin'] ?? false
        );

        $superAdministratorExists = User::query()
            ->withTrashed()
            ->where('is_super_admin', true)
            ->when(
                $user !== null,
                static fn($query) => $query->whereKeyNot($user->id)
            )
            ->exists();

        if (
            $isSuperAdministrator
            && $superAdministratorExists
        ) {
            throw new SuperAdministratorAlreadyExistsException(
                'Системата вече има супер администратор.'
            );
        }

        $data = [
            'fullname' => $fullname,
            'email' => $email,
            'is_active' => $this->toBoolean(
                $input['is_active'] ?? false
            ),
            'is_super_admin' => $isSuperAdministrator,
        ];

        $password = (string) ($input['password'] ?? '');

        if ($password !== '') {
            if (strlen($password) < 12) {
                throw new InvalidArgumentException(
                    'Паролата трябва да е поне 12 символа.'
                );
            }

            $data['password'] = $password;
        } elseif ($user === null) {
            throw new InvalidArgumentException(
                'Паролата е задължителна.'
            );
        }

        return $data;
    }

    private function normalizeRoleIds(mixed $roles): array
    {
        if (!is_array($roles)) {
            return [];
        }

        $ids = array_is_list($roles)
            ? array_map(
                static fn(mixed $value): int => (int) $value,
                $roles
            )
            : array_map(
                static fn(mixed $key): int => (int) $key,
                array_keys(
                    array_filter(
                        $roles,
                        fn(mixed $value): bool => $this->toBoolean($value)
                    )
                )
            );

        $ids = array_values(
            array_unique(
                array_filter(
                    $ids,
                    static fn(int $id): bool => $id > 0
                )
            )
        );

        if ($ids === []) {
            return [];
        }

        $validIds = Role::query()
            ->where('is_active', true)
            ->whereIn('id', $ids)
            ->pluck('id')
            ->map(
                static fn($id): int => (int) $id
            )
            ->values()
            ->all();

        if (count($validIds) !== count($ids)) {
            throw new InvalidArgumentException(
                'Избрана е невалидна или неактивна роля.'
            );
        }

        return $validIds;
    }

    private function normalizeIds(mixed $ids): array
    {
        return array_values(
            array_unique(
                array_filter(
                    array_map(
                        static fn(mixed $id): int => (int) $id,
                        is_array($ids)
                            ? $ids
                            : []
                    ),
                    static fn(int $id): bool => $id > 0
                )
            )
        );
    }

    private function assertDeletable(
        User $user,
        ?User $actor
    ): void {
        if ($user->is_super_admin) {
            throw new InvalidArgumentException(
                'Супер администраторът не може да бъде изтрит.'
            );
        }

        if (
            $actor !== null
            && (int) $actor->id === (int) $user->id
        ) {
            throw new InvalidArgumentException(
                'Не можете да изтриете собствения си профил.'
            );
        }
    }

    private function assertBulkActionAllowed(
        string $action,
        array $ids,
        ?User $actor
    ): void {
        if (!in_array(
            $action,
            [
                'activate',
                'deactivate',
                'trash',
                'restore',
                'force-delete',
            ],
            true
        )) {
            throw new InvalidArgumentException(
                'Невалидно групово действие.'
            );
        }

        $selectedUsers = User::query()
            ->withTrashed()
            ->whereIn('id', $ids)
            ->get();

        if ($selectedUsers->count() !== count($ids)) {
            throw new InvalidArgumentException(
                'Един или повече потребители не съществуват.'
            );
        }

        if (
            in_array(
                $action,
                ['trash', 'force-delete'],
                true
            )
            && $selectedUsers->contains(
                static fn(User $user): bool => $user->is_super_admin
            )
        ) {
            throw new InvalidArgumentException(
                'Супер администраторът не може да бъде изтрит.'
            );
        }

        if (
            $actor !== null
            && in_array(
                $action,
                ['deactivate', 'trash', 'force-delete'],
                true
            )
            && in_array(
                (int) $actor->id,
                $ids,
                true
            )
        ) {
            throw new InvalidArgumentException(
                'Избраното действие не може да бъде приложено върху собствения ви профил.'
            );
        }
    }

    private function toBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return in_array(
            strtolower(
                (string) $value
            ),
            [
                '1',
                'true',
                'yes',
                'on',
            ],
            true
        );
    }
}
