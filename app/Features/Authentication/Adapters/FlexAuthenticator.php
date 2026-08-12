<?php

declare(strict_types=1);

namespace Flex\Features\Authentication\Adapters;

use Flex\Core\Auth;
use Flex\Features\Authentication\Contracts\AuthenticatorInterface;
use Flex\Features\Authentication\Models\User;

final class FlexAuthenticator implements AuthenticatorInterface
{
    public function check(): bool { return Auth::check(); }
    public function isAdmin(): bool
    {
        $legacyUser = Auth::user();

        if ($legacyUser === null) {
            return false;
        }

        $user = User::query()->find((int) $legacyUser->id);

        return $user !== null
            && $user->is_active
            && ($user->is_super_admin || $user->hasPermission('admin.access'));
    }
}
