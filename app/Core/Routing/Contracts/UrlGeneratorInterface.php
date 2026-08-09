<?php

declare(strict_types=1);

namespace Flex\Core\Routing\Contracts;

interface UrlGeneratorInterface
{
    /** @param array<string, mixed> $parameters */
    public function route(string $name, array $parameters = [], bool $absolute = true): string;
}
