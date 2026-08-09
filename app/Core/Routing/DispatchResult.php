<?php

declare(strict_types=1);

namespace Flex\Core\Routing;

use LogicException;

final readonly class DispatchResult
{
    /**
     * @param array<string, string> $parameters
     * @param list<string> $allowedMethods
     */
    private function __construct(
        public RouteMatchStatus $status,
        private ?Route $matchedRoute = null,
        private array $parameters = [],
        private array $allowedMethods = [],
    ) {
    }

    /** @param array<string, string> $parameters */
    public static function found(Route $route, array $parameters = []): self
    {
        return new self(RouteMatchStatus::Found, $route, $parameters);
    }

    /** @param list<string> $allowedMethods */
    public static function methodNotAllowed(array $allowedMethods): self
    {
        $allowedMethods = array_values(array_unique(array_map('strtoupper', $allowedMethods)));
        sort($allowedMethods);

        return new self(RouteMatchStatus::MethodNotAllowed, allowedMethods: $allowedMethods);
    }

    public static function notFound(): self
    {
        return new self(RouteMatchStatus::NotFound);
    }

    public function isFound(): bool
    {
        return $this->status === RouteMatchStatus::Found;
    }

    public function isMethodNotAllowed(): bool
    {
        return $this->status === RouteMatchStatus::MethodNotAllowed;
    }

    public function isNotFound(): bool
    {
        return $this->status === RouteMatchStatus::NotFound;
    }

    public function route(): Route
    {
        if ($this->matchedRoute === null) {
            throw new LogicException('This dispatch result does not contain a matched route.');
        }

        return $this->matchedRoute;
    }

    /** @return array<string, string> */
    public function parameters(): array
    {
        return $this->parameters;
    }

    public function parameter(string $name, ?string $default = null): ?string
    {
        return $this->parameters[$name] ?? $default;
    }

    /** @return list<string> */
    public function allowedMethods(): array
    {
        return $this->allowedMethods;
    }
}
