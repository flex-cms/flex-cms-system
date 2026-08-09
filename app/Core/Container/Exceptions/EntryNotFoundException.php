<?php

declare(strict_types=1);

namespace Flex\Core\Container\Exceptions;

use Psr\Container\NotFoundExceptionInterface;

final class EntryNotFoundException extends ContainerException implements NotFoundExceptionInterface
{
}
