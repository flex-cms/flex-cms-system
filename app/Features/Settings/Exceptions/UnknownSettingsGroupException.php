<?php

declare(strict_types=1);

namespace Flex\Features\Settings\Exceptions;

use InvalidArgumentException;

final class UnknownSettingsGroupException extends InvalidArgumentException
{
    public function __construct(
        private readonly string $group
    ) {
        parent::__construct(
            sprintf(
                'Unknown settings page group [%s].',
                $group
            )
        );
    }

    public function group(): string
    {
        return $this->group;
    }
}