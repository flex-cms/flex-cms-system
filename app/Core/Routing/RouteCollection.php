<?php

declare(strict_types=1);

namespace Flex\Core\Routing;

use Countable;
use Flex\Core\Routing\Exceptions\DuplicateRouteException;
use Flex\Core\Routing\Exceptions\DuplicateRouteNameException;
use IteratorAggregate;
use Traversable;

final class RouteCollection implements Countable, IteratorAggregate
{
    private array $routes = [];
    private array $byMethodAndUri = [];
    private array $byName = [];
    private int $version = 0;

    public function add(Route $route): Route
    {
        $this->synchronizeNames();
        foreach ($route->methods() as $method) {
            $key = $method . ' ' . $route->uri();
            if (isset($this->byMethodAndUri[$key])) { throw new DuplicateRouteException("Route [{$key}] is already registered."); }
        }
        $name = $route->getName();
        if ($name !== null && isset($this->byName[$name])) { throw new DuplicateRouteNameException("Route name [{$name}] is already registered."); }
        $this->routes[] = $route;
        foreach ($route->methods() as $method) { $this->byMethodAndUri[$method . ' ' . $route->uri()] = $route; }
        if ($name !== null) { $this->byName[$name] = $route; }
        $this->version++;
        return $route;
    }

    public function synchronizeNames(): void
    {
        $names = [];
        foreach ($this->routes as $route) {
            $name = $route->getName();
            if ($name === null) { continue; }
            if (isset($names[$name]) && $names[$name] !== $route) { throw new DuplicateRouteNameException("Route name [{$name}] is already registered."); }
            $names[$name] = $route;
        }
        $this->byName = $names;
    }

    public function refreshName(Route $route, ?string $previousName = null): void { $this->synchronizeNames(); $this->version++; }
    public function named(string $name): ?Route { $this->synchronizeNames(); return $this->byName[$name] ?? null; }
    public function hasNamed(string $name): bool { $this->synchronizeNames(); return isset($this->byName[$name]); }
    public function all(): array { return $this->routes; }
    public function version(): int { return $this->version; }
    public function count(): int { return count($this->routes); }
    public function getIterator(): Traversable { yield from $this->routes; }
}
