<?php

declare(strict_types=1);

namespace Flex\Features\Authentication\Adapters;

use Flex\Features\Authentication\Contracts\AuthenticatorInterface;
use Flex\Features\Authentication\Providers\AuthProvider;

final class FlexAuthenticator implements AuthenticatorInterface
{
    public function check(): bool { return AuthProvider::check(); }
    public function isAdmin(): bool
    {
        return AuthProvider::isAdmin();
    }
}
