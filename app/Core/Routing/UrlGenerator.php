<?php

declare(strict_types=1);

namespace Flex\Core\Routing;

use Flex\Core\Routing\Contracts\UrlGeneratorInterface;
use Flex\Core\Routing\Exceptions\RouteGenerationException;

final readonly class UrlGenerator implements UrlGeneratorInterface
{
    public function __construct(
        private RouteCollection $routes,
        private string $baseUrl = '',
    ) {
    }

    public function route(string $name, array $parameters = [], bool $absolute = true): string
    {
        $route = $this->routes->named($name);
        if ($route === null) {
            throw new RouteGenerationException("Named route [{$name}] does not exist.");
        }

        $uri = $route->uri();
        $constraints = $route->constraints();
        $used = [];

        $segments = $uri === '/' ? [] : explode('/', trim($uri, '/'));
        $generated = [];

        foreach ($segments as $segment) {
            if (preg_match('/^\{([A-Za-z_][A-Za-z0-9_]*)(\?)?\}$/', $segment, $match)) {
                $parameter = $match[1];
                $optional = ($match[2] ?? '') === '?';

                if (!array_key_exists($parameter, $parameters) || $parameters[$parameter] === null) {
                    if ($optional) {
                        continue;
                    }

                    throw new RouteGenerationException(
                        "Missing required parameter [{$parameter}] for route [{$name}]."
                    );
                }

                $value = $this->stringify($parameters[$parameter], $parameter, $name);
                $this->assertConstraint($parameter, $value, $constraints[$parameter] ?? null, $name);
                $generated[] = rawurlencode($value);
                $used[$parameter] = true;
                continue;
            }

            $generated[] = preg_replace_callback(
                '/\{([A-Za-z_][A-Za-z0-9_]*)\}/',
                function (array $match) use ($parameters, $constraints, $name, &$used): string {
                    $parameter = $match[1];
                    if (!array_key_exists($parameter, $parameters) || $parameters[$parameter] === null) {
                        throw new RouteGenerationException(
                            "Missing required parameter [{$parameter}] for route [{$name}]."
                        );
                    }

                    $value = $this->stringify($parameters[$parameter], $parameter, $name);
                    $this->assertConstraint($parameter, $value, $constraints[$parameter] ?? null, $name);
                    $used[$parameter] = true;

                    return rawurlencode($value);
                },
                $segment,
            );
        }

        $path = '/' . implode('/', $generated);
        $path = $path === '' ? '/' : $path;

        $fragment = $parameters['_fragment'] ?? null;
        unset($parameters['_fragment']);

        $query = array_diff_key($parameters, $used);
        $query = array_filter($query, static fn (mixed $value): bool => $value !== null);
        if ($query !== []) {
            $path .= '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
        }

        if ($fragment !== null && $fragment !== '') {
            $path .= '#' . rawurlencode((string) $fragment);
        }

        if (!$absolute || $this->baseUrl === '') {
            return $path;
        }

        return rtrim($this->baseUrl, '/') . $path;
    }

    private function stringify(mixed $value, string $parameter, string $route): string
    {
        if ($value instanceof \BackedEnum) {
            $value = $value->value;
        } elseif ($value instanceof \Stringable) {
            $value = (string) $value;
        }

        if (!is_scalar($value) || is_bool($value)) {
            throw new RouteGenerationException(
                "Parameter [{$parameter}] for route [{$route}] must be a scalar or stringable value."
            );
        }

        return (string) $value;
    }

    private function assertConstraint(
        string $parameter,
        string $value,
        ?string $constraint,
        string $route,
    ): void {
        if ($constraint === null) {
            return;
        }

        $delimiter = $this->regexDelimiter($constraint);
        $result = @preg_match(
            $delimiter . '^(?:' . $constraint . ')$' . $delimiter . 'D',
            $value,
        );

        if ($result === false) {
            throw new RouteGenerationException(
                "Route [{$route}] has an invalid constraint for parameter [{$parameter}]."
            );
        }

        if ($result !== 1) {
            throw new RouteGenerationException(
                "Parameter [{$parameter}] does not satisfy the constraint for route [{$route}]."
            );
        }
    }

    private function regexDelimiter(string $pattern): string
    {
        foreach (['~', '#', '%', '!', '@'] as $delimiter) {
            if (!str_contains($pattern, $delimiter)) {
                return $delimiter;
            }
        }

        throw new RouteGenerationException('Unable to safely delimit a route constraint.');
    }
}
