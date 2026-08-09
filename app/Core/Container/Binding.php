<?php

declare(strict_types=1);

namespace Flex\Core\Container;

use Closure;

final readonly class Binding
{
    public function __construct(
        public string|Closure $concrete,
        public bool $shared = false,
    ) {
    }
}
