<?php

declare(strict_types=1);

namespace Flex\Core\Routing;

use BackedEnum;
use Closure;
use Flex\Core\Routing\Exceptions\InvalidRouteActionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;
use Throwable;

final class RouteParameterBinder
{
    /**
     * @param callable|array|string $action
     * @param array<string, string> $parameters
     * @return array<string, mixed>
     */
    public function bind(callable|array|string $action, array $parameters): array
    {
        $reflection = $this->reflect($action);
        $bound = [];

        foreach ($reflection->getParameters() as $parameter) {
            $name = $parameter->getName();
            if (!array_key_exists($name, $parameters)) {
                continue;
            }

            $bound[$name] = $this->convert($parameters[$name], $parameter);
        }

        return $bound;
    }

    private function reflect(callable|array|string $action): ReflectionFunctionAbstract
    {
        try {
            if ($action instanceof Closure) {
                return new ReflectionFunction($action);
            }

            if (is_array($action) && count($action) === 2) {
                return new ReflectionMethod($action[0], $action[1]);
            }

            if (is_string($action) && str_contains($action, '@')) {
                [$class, $method] = explode('@', $action, 2);

                return new ReflectionMethod($class, $method);
            }

            if (is_string($action) && class_exists($action)) {
                return new ReflectionMethod($action, '__invoke');
            }

            return new ReflectionFunction(Closure::fromCallable($action));
        } catch (Throwable $exception) {
            throw new InvalidRouteActionException(
                'The route action cannot be reflected: ' . $exception->getMessage(),
                previous: $exception,
            );
        }
    }

    private function convert(string $value, ReflectionParameter $parameter): mixed
    {
        $type = $parameter->getType();

        if ($type instanceof ReflectionUnionType) {
            foreach ($type->getTypes() as $candidate) {
                if ($candidate instanceof ReflectionNamedType && $candidate->getName() !== 'null') {
                    try {
                        return $this->convertNamed($value, $candidate, $parameter);
                    } catch (InvalidRouteActionException) {
                        continue;
                    }
                }
            }

            throw $this->conversionException($value, $parameter);
        }

        if (!$type instanceof ReflectionNamedType) {
            return $value;
        }

        return $this->convertNamed($value, $type, $parameter);
    }

    private function convertNamed(
        string $value,
        ReflectionNamedType $type,
        ReflectionParameter $parameter,
    ): mixed {
        $name = $type->getName();

        if (!$type->isBuiltin() && is_subclass_of($name, BackedEnum::class)) {
            try {
                return $name::from($value);
            } catch (Throwable) {
                throw $this->conversionException($value, $parameter);
            }
        }

        return match ($name) {
            'string', 'mixed' => $value,
            'int' => filter_var($value, FILTER_VALIDATE_INT) !== false
                ? (int) $value
                : throw $this->conversionException($value, $parameter),
            'float' => is_numeric($value)
                ? (float) $value
                : throw $this->conversionException($value, $parameter),
            'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
                ?? throw $this->conversionException($value, $parameter),
            default => $value,
        };
    }

    private function conversionException(
        string $value,
        ReflectionParameter $parameter,
    ): InvalidRouteActionException {
        return new InvalidRouteActionException(sprintf(
            'Route parameter [%s] with value [%s] cannot be converted to the declared action type.',
            $parameter->getName(),
            $value,
        ));
    }
}
