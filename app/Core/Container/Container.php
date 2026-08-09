<?php

declare(strict_types=1);

namespace Flex\Core\Container;

use Closure;
use Flex\Core\Container\Contracts\ContainerInterface;
use Flex\Core\Container\Exceptions\CircularDependencyException;
use Flex\Core\Container\Exceptions\EntryNotFoundException;
use Flex\Core\Container\Exceptions\UnresolvableDependencyException;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;

final class Container implements ContainerInterface
{
    /** @var array<string, Binding> */
    private array $bindings = [];

    /** @var array<string, object> */
    private array $instances = [];

    /** @var array<string, string> */
    private array $aliases = [];

    /** @var string[] */
    private array $buildStack = [];

    public function bind(
        string $abstract,
        string|Closure|null $concrete = null,
        bool $shared = false,
    ): self {
        $abstract = $this->normalize($abstract);
        $concrete ??= $abstract;

        $this->bindings[$abstract] = new Binding($concrete, $shared);
        unset($this->instances[$abstract]);

        return $this;
    }

    public function singleton(string $abstract, string|Closure|null $concrete = null): self
    {
        return $this->bind($abstract, $concrete, true);
    }

    public function instance(string $abstract, object $instance): self
    {
        $abstract = $this->normalize($abstract);
        $this->instances[$abstract] = $instance;
        unset($this->bindings[$abstract]);

        return $this;
    }

    public function alias(string $abstract, string $alias): self
    {
        $abstract = $this->normalize($abstract);
        $alias = $this->normalize($alias);

        if ($abstract === $alias) {
            throw new UnresolvableDependencyException('A container entry cannot alias itself.');
        }

        $this->aliases[$alias] = $abstract;

        return $this;
    }

    public function get(string $id): mixed
    {
        if (!$this->has($id)) {
            throw new EntryNotFoundException("Container entry [{$id}] was not found.");
        }

        return $this->make($id);
    }

    public function has(string $id): bool
    {
        $id = $this->resolveAlias($this->normalize($id));

        return isset($this->instances[$id])
            || isset($this->bindings[$id])
            || class_exists($id);
    }

    public function make(string $abstract, array $parameters = []): mixed
    {
        $abstract = $this->resolveAlias($this->normalize($abstract));

        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        $binding = $this->bindings[$abstract] ?? new Binding($abstract);

        $object = $binding->concrete instanceof Closure
            ? $this->invokeFactory($binding->concrete, $parameters)
            : $this->build($binding->concrete, $parameters);

        if ($binding->shared && is_object($object)) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    public function call(callable|array|string $callback, array $parameters = []): mixed
    {
        [$callable, $reflection] = $this->reflectCallable($callback);
        $arguments = $this->resolveParameters($reflection, $parameters);

        return $callable(...$arguments);
    }

    private function build(string $concrete, array $parameters): object
    {
        if (in_array($concrete, $this->buildStack, true)) {
            $chain = implode(' -> ', [...$this->buildStack, $concrete]);
            throw new CircularDependencyException("Circular dependency detected: {$chain}.");
        }

        try {
            $reflection = new ReflectionClass($concrete);
        } catch (ReflectionException $exception) {
            throw new EntryNotFoundException("Class [{$concrete}] does not exist.", previous: $exception);
        }

        if (!$reflection->isInstantiable()) {
            throw new UnresolvableDependencyException("Class [{$concrete}] is not instantiable.");
        }

        $this->buildStack[] = $concrete;

        try {
            $constructor = $reflection->getConstructor();
            if ($constructor === null) {
                return $reflection->newInstance();
            }

            return $reflection->newInstanceArgs($this->resolveParameters($constructor, $parameters));
        } finally {
            array_pop($this->buildStack);
        }
    }

    private function invokeFactory(Closure $factory, array $parameters): mixed
    {
        $reflection = new ReflectionFunction($factory);
        $resolved = [];

        foreach ($reflection->getParameters() as $index => $parameter) {
            if ($index === 0 && $this->acceptsContainer($parameter)) {
                $resolved[] = $this;
                continue;
            }

            $resolved[] = $this->resolveParameter($parameter, $parameters);
        }

        return $factory(...$resolved);
    }

    /** @return array{0: callable, 1: ReflectionFunctionAbstract} */
    private function reflectCallable(callable|array|string $callback): array
    {
        try {
            if (is_string($callback) && str_contains($callback, '@')) {
                [$class, $method] = explode('@', $callback, 2);
                $instance = $this->make($class);

                return [[$instance, $method], new ReflectionMethod($instance, $method)];
            }

            if (is_array($callback)) {
                [$target, $method] = $callback;
                $target = is_string($target) ? $this->make($target) : $target;

                return [[$target, $method], new ReflectionMethod($target, $method)];
            }

            if (is_string($callback) && class_exists($callback)) {
                $instance = $this->make($callback);

                return [[$instance, '__invoke'], new ReflectionMethod($instance, '__invoke')];
            }

            $closure = Closure::fromCallable($callback);

            return [$closure, new ReflectionFunction($closure)];
        } catch (ReflectionException $exception) {
            throw new UnresolvableDependencyException('The requested callable cannot be reflected.', previous: $exception);
        }
    }

    /** @return list<mixed> */
    private function resolveParameters(ReflectionFunctionAbstract $reflection, array $parameters): array
    {
        $resolved = [];

        foreach ($reflection->getParameters() as $parameter) {
            if ($parameter->isVariadic()) {
                $values = $parameters[$parameter->getName()] ?? [];
                array_push($resolved, ...(is_array($values) ? $values : [$values]));
                continue;
            }

            $resolved[] = $this->resolveParameter($parameter, $parameters);
        }

        return $resolved;
    }

    private function resolveParameter(ReflectionParameter $parameter, array $parameters): mixed
    {
        $name = $parameter->getName();
        if (array_key_exists($name, $parameters)) {
            return $parameters[$name];
        }

        $type = $parameter->getType();

        if ($type instanceof ReflectionUnionType) {
            foreach ($type->getTypes() as $candidate) {
                if ($candidate instanceof ReflectionNamedType && !$candidate->isBuiltin() && $this->has($candidate->getName())) {
                    return $this->make($candidate->getName());
                }
            }
        } elseif ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
            return $this->make($type->getName());
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        if ($parameter->allowsNull()) {
            return null;
        }

        $owner = $parameter->getDeclaringClass()?->getName()
            ?? $parameter->getDeclaringFunction()->getName();

        throw new UnresolvableDependencyException(
            "Cannot resolve parameter \${$name} while building [{$owner}]."
        );
    }

    private function acceptsContainer(ReflectionParameter $parameter): bool
    {
        $type = $parameter->getType();

        return $type instanceof ReflectionNamedType
            && !$type->isBuiltin()
            && is_a($this, $type->getName());
    }

    private function resolveAlias(string $abstract): string
    {
        $visited = [];

        while (isset($this->aliases[$abstract])) {
            if (isset($visited[$abstract])) {
                throw new CircularDependencyException("Circular container alias detected at [{$abstract}].");
            }

            $visited[$abstract] = true;
            $abstract = $this->aliases[$abstract];
        }

        return $abstract;
    }

    private function normalize(string $id): string
    {
        return ltrim(trim($id), '\\');
    }
}
