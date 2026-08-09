<?php

declare(strict_types=1);

namespace Flex\Core\Container\Contracts;

use Closure;
use Psr\Container\ContainerInterface as PsrContainerInterface;

interface ContainerInterface extends PsrContainerInterface
{
    public function bind(string $abstract, string|Closure|null $concrete = null, bool $shared = false): self;

    public function singleton(string $abstract, string|Closure|null $concrete = null): self;

    public function instance(string $abstract, object $instance): self;

    public function alias(string $abstract, string $alias): self;

    public function make(string $abstract, array $parameters = []): mixed;

    public function call(callable|array|string $callback, array $parameters = []): mixed;
}
