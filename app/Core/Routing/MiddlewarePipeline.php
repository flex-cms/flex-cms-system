<?php

declare(strict_types=1);

namespace Flex\Core\Routing;

use Closure;
use Flex\Core\Container\Contracts\ContainerInterface;
use Flex\Core\Http\Contracts\MiddlewareInterface;
use Flex\Core\Http\Request;
use Flex\Core\Http\Response;
use Flex\Core\Routing\Exceptions\MiddlewareException;

final readonly class MiddlewarePipeline
{
    public function __construct(
        private ContainerInterface $container,
        private MiddlewareRegistry $registry,
    ) {
    }

    /**
     * @param list<string> $middleware
     * @param Closure(Request): Response $destination
     */
    public function process(Request $request, array $middleware, Closure $destination): Response
    {
        $next = new CallableRequestHandler($destination);

        foreach (array_reverse($middleware) as $definition) {
            [$class, $parameters] = $this->registry->resolve($definition);
            $instance = $this->container->make($class);

            if (!$instance instanceof MiddlewareInterface) {
                throw new MiddlewareException(sprintf(
                    'Middleware [%s] must implement [%s].',
                    $class,
                    MiddlewareInterface::class,
                ));
            }

            $downstream = $next;
            $next = new CallableRequestHandler(
                static fn (Request $current): Response => $instance->process(
                    $current,
                    $downstream,
                    ...$parameters,
                ),
            );
        }

        return $next->handle($request);
    }
}
