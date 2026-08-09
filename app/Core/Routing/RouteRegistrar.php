<?php

declare(strict_types=1);

namespace Flex\Core\Routing;

use Closure;
use Flex\Core\Routing\Exceptions\InvalidRouteException;

final class RouteRegistrar
{
    /** @var list<array{prefix: string, name: string, middleware: list<string>, without_middleware: list<string>}> */
    private array $groupStack = [];

    public function __construct(private readonly RouteCollection $routes)
    {
    }

    public function collection(): RouteCollection
    {
        return $this->routes;
    }

    public function add(string|array $methods, string $uri, mixed $action): Route
    {
        $attributes = $this->mergedGroupAttributes();
        $route = new Route(
            $methods,
            self::joinUris($attributes['prefix'], $uri),
            $action,
        );

        if ($attributes['name'] !== '') {
            $route->namePrefix($attributes['name']);
        }

        if ($attributes['middleware'] !== []) {
            $route->middleware($attributes['middleware']);
        }

        if ($attributes['without_middleware'] !== []) {
            $route->withoutMiddleware($attributes['without_middleware']);
        }

        return $this->routes->add($route);
    }

    public function get(string $uri, mixed $action): Route
    {
        return $this->add('GET', $uri, $action);
    }

    public function post(string $uri, mixed $action): Route
    {
        return $this->add('POST', $uri, $action);
    }

    public function put(string $uri, mixed $action): Route
    {
        return $this->add('PUT', $uri, $action);
    }

    public function patch(string $uri, mixed $action): Route
    {
        return $this->add('PATCH', $uri, $action);
    }

    public function delete(string $uri, mixed $action): Route
    {
        return $this->add('DELETE', $uri, $action);
    }

    public function options(string $uri, mixed $action): Route
    {
        return $this->add('OPTIONS', $uri, $action);
    }

    /** @param list<string> $methods */
    public function match(array $methods, string $uri, mixed $action): Route
    {
        return $this->add($methods, $uri, $action);
    }

    public function any(string $uri, mixed $action): Route
    {
        return $this->add(
            ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
            $uri,
            $action,
        );
    }

    public function prefix(string $prefix): RouteGroup
    {
        return (new RouteGroup($this))->prefix($prefix);
    }

    public function name(string $prefix): RouteGroup
    {
        return (new RouteGroup($this))->name($prefix);
    }

    public function middleware(string|array ...$middleware): RouteGroup
    {
        return (new RouteGroup($this))->middleware(...$middleware);
    }

    public function withoutMiddleware(string|array ...$middleware): RouteGroup
    {
        return (new RouteGroup($this))->withoutMiddleware(...$middleware);
    }

    /**
     * @param array{prefix?: string, name?: string, middleware?: list<string>, without_middleware?: list<string>} $attributes
     */
    public function group(array $attributes, Closure|string $routes): void
    {
        $this->groupStack[] = [
            'prefix' => (string) ($attributes['prefix'] ?? ''),
            'name' => (string) ($attributes['name'] ?? ''),
            'middleware' => array_values($attributes['middleware'] ?? []),
            'without_middleware' => array_values($attributes['without_middleware'] ?? []),
        ];

        try {
            if ($routes instanceof Closure) {
                $routes();
                return;
            }

            if (!is_file($routes)) {
                throw new InvalidRouteException("Route group file [{$routes}] does not exist.");
            }

            require $routes;
        } finally {
            array_pop($this->groupStack);
        }
    }

    public static function joinUris(string $prefix, string $uri): string
    {
        $joined = trim($prefix, '/') . '/' . trim($uri, '/');
        $joined = trim($joined, '/');

        return $joined === '' ? '/' : '/' . $joined;
    }

    /** @return array{prefix: string, name: string, middleware: list<string>, without_middleware: list<string>} */
    private function mergedGroupAttributes(): array
    {
        $merged = [
            'prefix' => '',
            'name' => '',
            'middleware' => [],
            'without_middleware' => [],
        ];

        foreach ($this->groupStack as $group) {
            $merged['prefix'] = self::joinUris($merged['prefix'], $group['prefix']);
            $merged['name'] .= $group['name'];
            $merged['middleware'] = array_values(array_unique([
                ...$merged['middleware'],
                ...$group['middleware'],
            ]));
            $merged['without_middleware'] = array_values(array_unique([
                ...$merged['without_middleware'],
                ...$group['without_middleware'],
            ]));
        }

        $merged['middleware'] = array_values(array_filter(
            $merged['middleware'],
            static fn (string $middleware): bool => !in_array(
                $middleware,
                $merged['without_middleware'],
                true,
            ),
        ));

        return $merged;
    }
}
