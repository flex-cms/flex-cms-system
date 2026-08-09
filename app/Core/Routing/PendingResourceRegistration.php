<?php

declare(strict_types=1);

namespace Flex\Core\Routing;

use Flex\Core\Routing\Exceptions\InvalidRouteException;

final class PendingResourceRegistration
{
    private const ACTIONS = ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'];

    /** @var list<string> */
    private array $actions;
    /** @var array<string, string> */
    private array $names = [];
    /** @var list<string> */
    private array $middleware = [];
    private ?string $parameter = null;
    private bool $registered = false;

    public function __construct(
        private readonly RouteRegistrar $registrar,
        private readonly string $name,
        private readonly string $controller,
        bool $api = false,
    ) {
        $this->actions = $api
            ? ['index', 'store', 'show', 'update', 'destroy']
            : self::ACTIONS;
    }

    /** @param list<string> $actions */
    public function only(array $actions): self
    {
        $this->assertActions($actions);
        $this->actions = array_values(array_intersect(self::ACTIONS, $actions));
        return $this;
    }

    /** @param list<string> $actions */
    public function except(array $actions): self
    {
        $this->assertActions($actions);
        $this->actions = array_values(array_diff($this->actions, $actions));
        return $this;
    }

    /** @param array<string, string> $names */
    public function names(array $names): self
    {
        $this->assertActions(array_keys($names));
        $this->names = array_replace($this->names, $names);
        return $this;
    }

    public function parameter(string $parameter): self
    {
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $parameter)) {
            throw new InvalidRouteException("Invalid resource parameter [{$parameter}].");
        }
        $this->parameter = $parameter;
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

    /** @return array<string, Route> */
    public function register(): array
    {
        if ($this->registered) {
            return [];
        }
        $this->registered = true;

        $uri = '/' . trim($this->name, '/');
        $routeName = str_replace('/', '.', trim($this->name, '/'));
        $segments = explode('/', trim($this->name, '/'));
        $resourceSegment = (string) end($segments);
        $parameter = $this->parameter ?? $this->singular($resourceSegment);
        $routes = [];

        $definitions = [
            'index' => ['GET', $uri],
            'create' => ['GET', $uri . '/create'],
            'store' => ['POST', $uri],
            'show' => ['GET', $uri . '/{' . $parameter . '}'],
            'edit' => ['GET', $uri . '/{' . $parameter . '}/edit'],
            'update' => [['PUT', 'PATCH'], $uri . '/{' . $parameter . '}'],
            'destroy' => ['DELETE', $uri . '/{' . $parameter . '}'],
        ];

        foreach (self::ACTIONS as $action) {
            if (!in_array($action, $this->actions, true)) {
                continue;
            }
            [$methods, $path] = $definitions[$action];
            $route = $this->registrar->add($methods, $path, [$this->controller, $action])
                ->name($this->names[$action] ?? $routeName . '.' . $action);
            if ($this->middleware !== []) {
                $route->middleware($this->middleware);
            }
            $routes[$action] = $route;
        }

        return $routes;
    }

    public function __destruct()
    {
        $this->register();
    }

    /** @param list<string> $actions */
    private function assertActions(array $actions): void
    {
        $invalid = array_values(array_diff($actions, self::ACTIONS));
        if ($invalid !== []) {
            throw new InvalidRouteException('Invalid resource actions: ' . implode(', ', $invalid) . '.');
        }
    }

    private function singular(string $value): string
    {
        if (str_ends_with($value, 'ies')) {
            return substr($value, 0, -3) . 'y';
        }
        return str_ends_with($value, 's') ? substr($value, 0, -1) : $value;
    }
}
