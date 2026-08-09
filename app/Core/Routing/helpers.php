<?php

declare(strict_types=1);

use Flex\Core\Routing\FlexRouter;

if (!function_exists('route')) {
    /** @param array<string, mixed> $parameters */
    function route(string $name, array $parameters = [], bool $absolute = true): string
    {
        return FlexRouter::route($name, $parameters, $absolute);
    }
}
