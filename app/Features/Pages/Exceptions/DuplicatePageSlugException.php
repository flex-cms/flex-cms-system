<?php

declare(strict_types=1);

namespace Flex\Features\Pages\Exceptions;

use DomainException;

final class DuplicatePageSlugException extends DomainException
{
    public function __construct(string $slug)
    {
        parent::__construct(
            sprintf('Page slug [%s] is already used under the selected parent.', $slug)
        );
    }
}
