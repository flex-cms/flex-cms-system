<?php

declare(strict_types=1);

namespace Flex\Features\Authentication\Data;

use Flex\Features\Authentication\Models\User;

final readonly class UsersPageData
{
    public function __construct(
        public string $title,
        public array $users
    ) {
    }

    public static function fromUsers(
        iterable $users
    ): self {
        $rows = [];

        foreach ($users as $user) {
            if (!$user instanceof User) {
                continue;
            }

            $rows[] = [
                'id' => (int) $user->id,
                'fullname' => $user->fullname ?: 'Без име',
                'email' => (string) $user->email,
                'roles' => $user->roles
                    ->pluck('name')
                    ->implode(', '),
                'is_active' => (bool) $user->is_active,
                'is_super_admin' => (bool) $user->is_super_admin,
                'status' => $user->is_active
                    ? 'active'
                    : 'inactive',
            ];
        }

        return new self(
            'Потребители',
            $rows
        );
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'users' => $this->users,
        ];
    }
}
