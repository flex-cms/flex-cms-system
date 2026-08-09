<?php

declare(strict_types=1);

namespace Flex\Core\Routing;

use Flex\Core\Routing\Exceptions\MiddlewareException;

final class MiddlewareRegistry
{
    /** @var array<string, class-string> */
    private array $aliases = [];

    /** @var list<string> */
    private array $global = [];

    /** @param class-string $middleware */
    public function alias(string $alias, string $middleware): self
    {
        $alias = trim($alias);
        if ($alias === '' || str_contains($alias, ':') || str_contains($alias, ',')) {
            throw new MiddlewareException("Invalid middleware alias [{$alias}].");
        }

        $this->aliases[$alias] = ltrim($middleware, '\\');

        return $this;
    }

    public function appendGlobal(string ...$middleware): self
    {
        array_push($this->global, ...$middleware);
        $this->global = array_values(array_unique($this->global));

        return $this;
    }

    public function prependGlobal(string ...$middleware): self
    {
        $this->global = array_values(array_unique([...$middleware, ...$this->global]));

        return $this;
    }

    /** @return list<string> */
    public function global(): array
    {
        return $this->global;
    }

    /** @return array{0: class-string, 1: list<string>} */
    public function resolve(string $definition): array
    {
        [$name, $parameterString] = array_pad(explode(':', $definition, 2), 2, null);
        $name = trim($name);
        $class = $this->aliases[$name] ?? ltrim($name, '\\');

        if ($class === '' || !class_exists($class)) {
            throw new MiddlewareException("Middleware [{$name}] is not registered or does not exist.");
        }

        $parameters = $parameterString === null || trim($parameterString) === ''
            ? []
            : array_values(array_map('trim', explode(',', $parameterString)));

        return [$class, $parameters];
    }
}
