<?php

declare(strict_types=1);

namespace Flex\Core\Routing;

use Flex\Core\Container\Contracts\ContainerInterface;
use Flex\Core\Http\Request;
use Flex\Core\Http\Response;
use Throwable;

final readonly class ControllerDispatcher
{
    public function __construct(
        private ContainerInterface $container,
        private RouteParameterBinder $parameterBinder = new RouteParameterBinder(),
        private ActionResultNormalizer $normalizer = new ActionResultNormalizer(),
    ) {
    }

    public function dispatch(DispatchResult $match, Request $request): Response
    {
        if (!$match->isFound()) {
            throw new \LogicException('Only a found dispatch result can execute a route action.');
        }

        $route = $match->route();
        $routeParameters = $match->parameters();
        $boundRequest = $request->withAttribute('_route_parameters', $routeParameters)
            ->withAttribute('_route', $route);

        $this->container->instance(Request::class, $boundRequest);

        $parameters = $this->parameterBinder->bind($route->action(), $routeParameters);

        $initialBufferLevel = ob_get_level();
        ob_start();

        try {
            $result = $this->container->call($route->action(), $parameters);
            $output = (string) ob_get_clean();
        } catch (Throwable $exception) {
            while (ob_get_level() > $initialBufferLevel) {
                ob_end_clean();
            }

            throw $exception;
        }

        return $this->normalizer->normalize($result, $output);
    }
}
