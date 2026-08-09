<?php

declare(strict_types=1);

namespace Flex\Core\Routing;

use LogicException;

final class FlexRouter
{
    private static ?RouteRegistrar $registrar = null;
    private static ?UrlGenerator $urlGenerator = null;

    public static function setRegistrar(RouteRegistrar $registrar): void { self::$registrar = $registrar; }
    public static function setUrlGenerator(UrlGenerator $generator): void { self::$urlGenerator = $generator; }
    public static function reset(): void { self::$registrar = null; self::$urlGenerator = null; }
    public static function registrar(): RouteRegistrar
    {
        return self::$registrar ?? throw new LogicException('FlexRouter has not been bootstrapped with a RouteRegistrar.');
    }
    public static function urlGenerator(): UrlGenerator
    {
        return self::$urlGenerator ?? throw new LogicException('FlexRouter has not been bootstrapped with a UrlGenerator.');
    }
    public static function route(string $name, array $parameters = [], bool $absolute = true): string { return self::urlGenerator()->route($name, $parameters, $absolute); }
    public static function get(string $uri, mixed $action): Route { return self::registrar()->get($uri, $action); }
    public static function post(string $uri, mixed $action): Route { return self::registrar()->post($uri, $action); }
    public static function put(string $uri, mixed $action): Route { return self::registrar()->put($uri, $action); }
    public static function patch(string $uri, mixed $action): Route { return self::registrar()->patch($uri, $action); }
    public static function delete(string $uri, mixed $action): Route { return self::registrar()->delete($uri, $action); }
    public static function options(string $uri, mixed $action): Route { return self::registrar()->options($uri, $action); }
    public static function match(array $methods, string $uri, mixed $action): Route { return self::registrar()->match($methods, $uri, $action); }
    public static function any(string $uri, mixed $action): Route { return self::registrar()->any($uri, $action); }
    public static function prefix(string $prefix): RouteGroup { return self::registrar()->prefix($prefix); }
    public static function name(string $prefix): RouteGroup { return self::registrar()->name($prefix); }
    public static function middleware(string|array ...$middleware): RouteGroup { return self::registrar()->middleware(...$middleware); }
    public static function withoutMiddleware(string|array ...$middleware): RouteGroup { return self::registrar()->withoutMiddleware(...$middleware); }
    public static function resource(string $name, string $controller): PendingResourceRegistration
    {
        return new PendingResourceRegistration(self::registrar(), $name, $controller);
    }
    public static function apiResource(string $name, string $controller): PendingResourceRegistration
    {
        return new PendingResourceRegistration(self::registrar(), $name, $controller, api: true);
    }
}
