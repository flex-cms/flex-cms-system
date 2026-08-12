<?php

declare(strict_types=1);

namespace Flex\Features\Authentication\Services;

use Flex\Core\Auth;
use Flex\Features\Authentication\Models\User;

final class AuthorizationService
{
    public function currentUser(): ?User
    {
        $legacyUser = Auth::user();
        return $legacyUser === null ? null : User::query()->find((int) $legacyUser->id);
    }

    public function allows(?User $user, string $permission): bool
    {
        return $user !== null && $user->is_active && ($user->is_super_admin || $user->hasPermission($permission));
    }

    public function denies(?User $user, string $permission): bool { return !$this->allows($user, $permission); }
}
