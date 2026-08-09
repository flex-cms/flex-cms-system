<?php

declare(strict_types=1);

namespace Flex\Features\Authentication\Contracts;

interface AuthenticatorInterface
{
    public function check(): bool;
    public function isAdmin(): bool;
}
