<?php

declare(strict_types=1);

namespace Flex\Features\Authentication\Services;

use Flex\Features\Authentication\Exceptions\SuperAdministratorAlreadyExistsException;
use Flex\Features\Authentication\Models\User;
use Flex\Features\Authentication\Repositories\UserRepositoryInterface;
use InvalidArgumentException;

final readonly class UserService
{
    public function __construct(private UserRepositoryInterface $users) {}

    public function save(?User $user, array $input): User
    {
        $email = strtolower(trim((string) ($input['email'] ?? '')));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { throw new InvalidArgumentException('Невалиден имейл адрес.'); }
        $duplicate = User::query()->where('email', $email)->when($user, fn ($q) => $q->whereKeyNot($user->id))->exists();
        if ($duplicate) { throw new InvalidArgumentException('Вече има потребител с този имейл.'); }

        $super = (bool) ($input['is_super_admin'] ?? false);
        if ($super && User::query()->where('is_super_admin', true)->when($user, fn ($q) => $q->whereKeyNot($user->id))->exists()) {
            throw new SuperAdministratorAlreadyExistsException('Системата вече има супер администратор.');
        }

        $data = [
            'fullname' => trim((string) ($input['fullname'] ?? '')),
            'email' => $email,
            'is_active' => (bool) ($input['is_active'] ?? false),
            'is_super_admin' => $super,
        ];
        if (!empty($input['password'])) {
            if (strlen((string) $input['password']) < 12) { throw new InvalidArgumentException('Паролата трябва да е поне 12 символа.'); }
            $data['password'] = (string) $input['password'];
        } elseif ($user === null) { throw new InvalidArgumentException('Паролата е задължителна.'); }

        $saved = $user === null ? $this->users->create($data) : $this->users->update($user, $data);
        $roleIds = array_values(array_filter(array_map('intval', (array) ($input['roles'] ?? []))));
        $saved->roles()->sync($super ? [] : $roleIds);
        return $saved->load('roles');
    }

    public function delete(User $user, ?User $actor): void
    {
        if ($user->is_super_admin) { throw new InvalidArgumentException('Супер администраторът не може да бъде изтрит.'); }
        if ($actor?->id === $user->id) { throw new InvalidArgumentException('Не можете да изтриете собствения си профил.'); }
        $user->delete();
    }
}
