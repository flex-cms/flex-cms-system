<?php

declare(strict_types=1);

namespace Flex\Features\Authentication\Services;

use Flex\Features\Authentication\Models\User;
use Flex\Features\Authentication\Providers\AuthProvider;

final class AuthorizationService
{
    public function currentUser(): ?User
    {
        return AuthProvider::user();
    }

    public function allows(?User $user, string $permission): bool
    {
        return $user !== null && $user->is_active && ($user->is_super_admin || $user->hasPermission($permission));
    }

    public function denies(?User $user, string $permission): bool { return !$this->allows($user, $permission); }
}
