<?php

declare(strict_types=1);

namespace Flex\Features\Authentication\Exceptions;

use RuntimeException;

final class AuthorizationException extends RuntimeException
{
    public function __construct(
        public readonly string $permission
    ) {
        parent::__construct(
            sprintf(
                'Нямате разрешение за операцията [%s].',
                $permission
            )
        );
    }
}
