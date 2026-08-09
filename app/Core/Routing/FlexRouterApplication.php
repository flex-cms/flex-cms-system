<?php

declare(strict_types=1);

namespace Flex\Core\Routing;

use Closure;
use Flex\Core\Container\Container;
use Flex\Core\Container\Contracts\ContainerInterface;
use Flex\Core\Http\Contracts\ExceptionHandlerInterface;
use Flex\Core\Http\ExceptionHandler;
use Throwable;

final readonly class FlexRouterApplication
{
    private function __construct(
        public Container $container,
        public RouteCollection $routes,
        public RouteRegistrar $registrar,
        public MiddlewareRegistry $middleware,
        public UrlGenerator $urls,
        public FlexRouterKernel $kernel,
    ) {}

    /** @param null|Closure(Throwable): void $logger */
    public static function create(
        string $baseUrl = '',
        string $basePath = '',
        bool $passNotFound = true,
        bool $debug = false,
        ?Closure $logger = null,
    ): self {
        $container = new Container();
        $container->instance(Container::class, $container);
        $container->instance(ContainerInterface::class, $container);
        $exceptions = new ExceptionHandler($debug, $logger);
        $container->instance(ExceptionHandlerInterface::class, $exceptions);
        $routes = new RouteCollection();
        $registrar = new RouteRegistrar($routes);
        $middleware = new MiddlewareRegistry();
        $urls = new UrlGenerator($routes, $baseUrl);
        FlexRouter::setRegistrar($registrar);
        FlexRouter::setUrlGenerator($urls);
        $matcher = new RouteMatcher($routes, $basePath);
        $dispatcher = new ControllerDispatcher($container);
        $pipeline = new MiddlewarePipeline($container, $middleware);
        $runner = new RouteRunner($dispatcher, $pipeline, $middleware);
        $kernel = new FlexRouterKernel($matcher, $runner, $exceptions, $passNotFound);
        return new self($container, $routes, $registrar, $middleware, $urls, $kernel);
    }

    public function featureRoutes(string $featuresPath, ?array $enabledFeatures = null, array $disabledFeatures = []): FeatureRouteLoader
    {
        return new FeatureRouteLoader($this->registrar, $featuresPath, $enabledFeatures, $disabledFeatures);
    }
}
