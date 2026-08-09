<?php

declare(strict_types=1);

namespace Flex\Features\Authentication\Contracts;

interface LoginUrlResolverInterface
{
    public function loginUrl(): string;
}
