<?php

declare(strict_types=1);

namespace Flex\Core\Routing;

use Closure;
use Flex\Core\Routing\Exceptions\InvalidRouteException;

final class Route
{
    private const METHOD_PATTERN = '/^[A-Z][A-Z0-9_-]*$/';
    private const PARAMETER_PATTERN = '/\{([A-Za-z_][A-Za-z0-9_]*)(\?)?\}/';

    /** @var list<string> */
    private array $methods;
    /** @var array<string, string> */
    private array $constraints = [];
    /** @var list<string|class-string> */
    private array $middleware = [];
    private ?string $routeName = null;
    private string $routeNamePrefix = '';
    private bool $excludedFromCsrf = false;

    public function __construct(
        string|array $methods,
        private readonly string $uri,
        private readonly mixed $action,
    ) {
        $this->methods = $this->normalizeMethods((array) $methods);
        $this->assertValidUri($uri);
        $this->assertValidAction($action);
    }

    public function methods(): array { return $this->methods; }
    public function uri(): string
    {
        $normalized = '/' . trim(preg_replace('#/+#', '/', $this->uri) ?? '/', '/');
        return $normalized === '' ? '/' : $normalized;
    }
    public function action(): mixed { return $this->action; }

    public function namePrefix(string $prefix): self
    {
        $this->routeNamePrefix .= trim($prefix);
        return $this;
    }

    public function name(string $name): self
    {
        $name = trim($name);
        if ($name === '' || preg_match('/\s/', $name)) {
            throw new InvalidRouteException('A route name must be non-empty and cannot contain whitespace.');
        }
        $this->routeName = $this->routeNamePrefix . $name;
        return $this;
    }

    public function getName(): ?string { return $this->routeName; }

    public function middleware(string|array ...$middleware): self
    {
        foreach ($middleware as $item) {
            foreach ((array) $item as $entry) {
                if (!is_string($entry) || trim($entry) === '') {
                    throw new InvalidRouteException('Route middleware must be a non-empty string.');
                }
                $this->middleware[] = trim($entry);
            }
        }
        $this->middleware = array_values(array_unique($this->middleware));
        return $this;
    }

    public function getMiddleware(): array { return $this->middleware; }
    public function withoutMiddleware(string|array ...$middleware): self
    {
        $excluded = [];
        foreach ($middleware as $item) { array_push($excluded, ...(array) $item); }
        $this->middleware = array_values(array_filter(
            $this->middleware,
            static fn (string $entry): bool => !in_array($entry, $excluded, true),
        ));
        return $this;
    }
    public function withoutCsrf(): self { $this->excludedFromCsrf = true; return $this; }
    public function excludesCsrf(): bool { return $this->excludedFromCsrf; }

    public function where(string|array $parameter, ?string $pattern = null): self
    {
        $constraints = is_array($parameter) ? $parameter : [$parameter => $pattern];
        foreach ($constraints as $name => $regex) {
            if (!in_array($name, $this->parameterNames(), true)) {
                throw new InvalidRouteException("Route parameter [{$name}] does not exist in [{$this->uri()}].");
            }
            if (!is_string($regex) || $regex === '') {
                throw new InvalidRouteException("Constraint for route parameter [{$name}] cannot be empty.");
            }
            $this->constraints[$name] = $regex;
        }
        return $this;
    }
    public function whereNumber(string ...$parameters): self { return $this->applyConstraint('[0-9]+', $parameters); }
    public function whereAlpha(string ...$parameters): self { return $this->applyConstraint('[A-Za-z]+', $parameters); }
    public function whereAlphaNumeric(string ...$parameters): self { return $this->applyConstraint('[A-Za-z0-9_-]+', $parameters); }
    public function whereUuid(string ...$parameters): self
    {
        return $this->applyConstraint('[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[1-5][0-9a-fA-F]{3}-[89abAB][0-9a-fA-F]{3}-[0-9a-fA-F]{12}', $parameters);
    }
    public function whereIn(string $parameter, array $values): self
    {
        if ($values === []) { throw new InvalidRouteException("Constraint values for [{$parameter}] cannot be empty."); }
        return $this->where($parameter, implode('|', array_map(static fn (mixed $value): string => preg_quote((string) $value, '~'), $values)));
    }
    public function constraints(): array { return $this->constraints; }
    public function parameterNames(): array
    {
        preg_match_all(self::PARAMETER_PATTERN, $this->uri(), $matches);
        return array_values(array_unique($matches[1] ?? []));
    }
    public function fastRoutePattern(): string
    {
        $segments = $this->uri() === '/' ? [] : explode('/', trim($this->uri(), '/'));
        $required = '';
        $optional = [];
        $optionalStarted = false;
        foreach ($segments as $segment) {
            if (preg_match('/^\{([A-Za-z_][A-Za-z0-9_]*)\?\}$/', $segment, $match)) {
                $optionalStarted = true;
                $optional[] = '/' . $this->compileParameter($match[1]);
                continue;
            }
            if ($optionalStarted) { throw new InvalidRouteException('Optional route parameters must be at the end of the URI.'); }
            $required .= '/' . preg_replace_callback(self::PARAMETER_PATTERN, fn (array $match): string => $this->compileParameter($match[1]), $segment);
        }
        $pattern = $required;
        foreach ($optional as $item) { $pattern .= '[' . $item; }
        $pattern .= str_repeat(']', count($optional));
        return $pattern === '' ? '/' : $pattern;
    }
    public function signature(): string
    {
        $methods = $this->methods; sort($methods);
        return implode('|', $methods) . ' ' . $this->uri();
    }
    private function applyConstraint(string $pattern, array $parameters): self
    {
        if ($parameters === []) { throw new InvalidRouteException('At least one route parameter is required.'); }
        foreach ($parameters as $parameter) { $this->where($parameter, $pattern); }
        return $this;
    }
    private function compileParameter(string $name): string
    {
        return isset($this->constraints[$name]) ? '{' . $name . ':' . $this->constraints[$name] . '}' : '{' . $name . '}';
    }
    private function normalizeMethods(array $methods): array
    {
        $normalized = [];
        foreach ($methods as $method) {
            $method = strtoupper(trim($method));
            if ($method === '' || !preg_match(self::METHOD_PATTERN, $method)) { throw new InvalidRouteException("Invalid HTTP method [{$method}]."); }
            $normalized[] = $method;
        }
        $normalized = array_values(array_unique($normalized));
        if ($normalized === []) { throw new InvalidRouteException('A route must have at least one HTTP method.'); }
        if (in_array('GET', $normalized, true) && !in_array('HEAD', $normalized, true)) { $normalized[] = 'HEAD'; }
        return $normalized;
    }
    private function assertValidUri(string $uri): void
    {
        if ($uri === '' || str_contains($uri, '#')) { throw new InvalidRouteException('A route URI must be a non-empty path without query string or fragment.'); }
        preg_match_all(self::PARAMETER_PATTERN, $uri, $matches);
        $names = $matches[1] ?? [];
        if (count($names) !== count(array_unique($names))) { throw new InvalidRouteException("Route [{$uri}] contains duplicate parameter names."); }
        foreach (explode('/', trim($uri, '/')) as $segment) {
            if (str_contains($segment, '?}') && !preg_match('/^\{[A-Za-z_][A-Za-z0-9_]*\?\}$/', $segment)) { throw new InvalidRouteException('An optional parameter must occupy an entire URI segment.'); }
        }
        $withoutParameters = preg_replace(self::PARAMETER_PATTERN, '', $uri);
        if (str_contains((string) $withoutParameters, '?')) { throw new InvalidRouteException('A route URI cannot contain a query string.'); }
        if (str_contains((string) $withoutParameters, '{') || str_contains((string) $withoutParameters, '}')) { throw new InvalidRouteException("Route [{$uri}] contains malformed parameters."); }
    }
    private function assertValidAction(mixed $action): void
    {
        if ($action instanceof Closure || is_string($action)) { return; }
        if (is_array($action) && count($action) === 2 && is_string($action[1])) { return; }
        if (is_callable($action)) { return; }
        throw new InvalidRouteException('A route action must be callable, a class name, or a [controller, method] pair.');
    }
}
