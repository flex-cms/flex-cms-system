<?php

declare(strict_types=1);

namespace Flex\Features\Authentication\Adapters;

use Flex\Features\Authentication\Contracts\LoginUrlResolverInterface;
use Flex\Models\Plugin;

final class FlexLoginUrlResolver implements LoginUrlResolverInterface
{
    public function loginUrl(): string
    {
        return Plugin::isActive('BasicAuthentication')
            ? '/auth/login'
            : '/login';
    }
}
