<?php

declare(strict_types=1);

namespace Flex\Core\Routing;

use FastRoute\RouteCollector;
use FastRoute\Dispatcher;
use Flex\Core\Http\Request;
use Flex\Core\Routing\Exceptions\RoutingException;
use Throwable;

use function FastRoute\simpleDispatcher;

final class RouteMatcher
{
    private ?Dispatcher $dispatcher = null;
    private int $compiledCollectionVersion = -1;

    public function __construct(
        private readonly RouteCollection $routes,
        private readonly string $basePath = '',
    ) {
    }

    public function match(Request $request): DispatchResult
    {
        return $this->matchMethodAndPath($request->method(), $request->path());
    }

    public function matchMethodAndPath(string $method, string $path): DispatchResult
    {
        $dispatcher = $this->dispatcher();
        $routeInfo = $dispatcher->dispatch(strtoupper($method), $this->normalizePath($path));

        return match ($routeInfo[0]) {
            Dispatcher::FOUND => $this->foundResult($routeInfo),
            Dispatcher::METHOD_NOT_ALLOWED => DispatchResult::methodNotAllowed($routeInfo[1]),
            Dispatcher::NOT_FOUND => DispatchResult::notFound(),
            default => throw new RoutingException('FastRoute returned an unknown dispatch status.'),
        };
    }

    public function invalidate(): void
    {
        $this->dispatcher = null;
        $this->compiledCollectionVersion = -1;
    }

    private function dispatcher(): Dispatcher
    {
        if (
            $this->dispatcher !== null
            && $this->compiledCollectionVersion === $this->routes->version()
        ) {
            return $this->dispatcher;
        }

        try {
            $this->dispatcher = simpleDispatcher(function (RouteCollector $collector): void {
                foreach ($this->routes as $route) {
                    $collector->addRoute(
                        $route->methods(),
                        $route->fastRoutePattern(),
                        $route,
                    );
                }
            });
        } catch (Throwable $exception) {
            throw new RoutingException(
                'The route collection could not be compiled by FastRoute: ' . $exception->getMessage(),
                previous: $exception,
            );
        }

        $this->compiledCollectionVersion = $this->routes->version();

        return $this->dispatcher;
    }

    /** @param array{0: int, 1: Route, 2: array<string, string>} $routeInfo */
    private function foundResult(array $routeInfo): DispatchResult
    {
        return DispatchResult::found($routeInfo[1], $routeInfo[2]);
    }

    private function normalizePath(string $path): string
    {
        $path = parse_url($path, PHP_URL_PATH);
        $path = is_string($path) ? rawurldecode($path) : '/';
        $path = '/' . trim(preg_replace('#/+#', '/', $path) ?? '/', '/');
        $path = $path === '' ? '/' : $path;

        $basePath = '/' . trim($this->basePath, '/');
        if ($basePath !== '/' && ($path === $basePath || str_starts_with($path, $basePath . '/'))) {
            $path = substr($path, strlen($basePath)) ?: '/';
        }

        return $path;
    }
}
