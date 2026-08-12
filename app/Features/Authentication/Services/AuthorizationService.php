<?php

declare(strict_types=1);

namespace Flex\Features\Authentication\Services;

use Flex\Features\Authentication\Exceptions\AuthorizationException;
use Flex\Features\Authentication\Models\User;
use Flex\Features\Authentication\Providers\AuthProvider;

final class AuthorizationService
{
    public function currentUser(): ?User
    {
        return AuthProvider::user();
    }

    public function can(string $permission): bool
    {
        return $this->allows(
            $this->currentUser(),
            $permission
        );
    }

    public function cannot(string $permission): bool
    {
        return !$this->can($permission);
    }

    public function canAny(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->can((string) $permission)) {
                return true;
            }
        }

        return false;
    }

    public function canAll(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->can((string) $permission)) {
                return false;
            }
        }

        return true;
    }

    public function allows(?User $user, string $permission): bool
    {
        if (
            $user === null
            || !$user->is_active
            || $permission === ''
        ) {
            return false;
        }

        return $user->isSuperAdministrator()
            || $user->hasPermission($permission);
    }

    public function denies(?User $user, string $permission): bool
    {
        return !$this->allows($user, $permission);
    }

    public function authorize(string $permission): void
    {
        if ($this->cannot($permission)) {
            throw new AuthorizationException($permission);
        }
    }
}
