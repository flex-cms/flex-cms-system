<?php

declare(strict_types=1);

namespace Flex\Core\Routing;

use Closure;

final class RouteGroup
{
    /** @var list<string> */
    private array $middleware = [];

    /** @var list<string> */
    private array $excludedMiddleware = [];

    public function __construct(
        private readonly RouteRegistrar $registrar,
        private string $prefix = '',
        private string $namePrefix = '',
    ) {
    }

    public function prefix(string $prefix): self
    {
        $this->prefix = RouteRegistrar::joinUris($this->prefix, $prefix);

        return $this;
    }

    public function name(string $prefix): self
    {
        $this->namePrefix .= trim($prefix);

        return $this;
    }

    public function middleware(string|array ...$middleware): self
    {
        foreach ($middleware as $item) {
            array_push($this->middleware, ...(array) $item);
        }

        $this->middleware = array_values(array_unique($this->middleware));

        return $this;
    }

    public function withoutMiddleware(string|array ...$middleware): self
    {
        foreach ($middleware as $item) {
            array_push($this->excludedMiddleware, ...(array) $item);
        }

        $this->excludedMiddleware = array_values(array_unique($this->excludedMiddleware));

        return $this;
    }

    public function group(Closure|string $routes): void
    {
        $this->registrar->group([
            'prefix' => $this->prefix,
            'name' => $this->namePrefix,
            'middleware' => $this->middleware,
            'without_middleware' => $this->excludedMiddleware,
        ], $routes);
    }
}
