<?php

declare(strict_types=1);

namespace Flex\Features\Authentication\Services;

use Flex\Features\Authentication\Models\User;
use Flex\Features\Authentication\Providers\AuthProvider;
use Flex\Features\Authentication\Support\AuthenticationTables;

final class AuthenticationService
{
    public function attemptAdministrator(
        string $email,
        string $password,
        bool $remember = false
    ): bool {
        $email = strtolower(trim($email));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
            return false;
        }

        if (!AuthProvider::attempt($email, $password, $remember)) {
            return false;
        }

        $user = User::query()
            ->with('roles.permissions')
            ->where('email', $email)
            ->first();

        if ($user === null || !$this->canAccessAdministration($user)) {
            AuthProvider::logout();

            return false;
        }

        return true;
    }

    public function canAccessAdministration(User $user): bool
    {
        if (!$user->is_active) {
            return false;
        }

        if ($user->is_super_admin) {
            return true;
        }

        return $user->roles()
            ->where(AuthenticationTables::roles() . '.is_active', true)
            ->whereHas(
                'permissions',
                static fn ($query) => $query
                    ->where(AuthenticationTables::permissions() . '.slug', 'admin.access')
                    ->where(AuthenticationTables::permissions() . '.is_active', true)
            )
            ->exists();
    }

    public function logout(): void
    {
        AuthProvider::logout();
    }
}
