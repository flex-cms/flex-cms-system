<?php

declare(strict_types=1);

namespace Flex\Features\Authentication\Adapters;

use Flex\Core\Auth;
use Flex\Features\Authentication\Contracts\AuthenticatorInterface;

final class FlexAuthenticator implements AuthenticatorInterface
{
    public function check(): bool { return Auth::check(); }
    public function isAdmin(): bool { return Auth::isAdmin(); }
}
