<?php

declare(strict_types=1);

namespace Flex\Core\Routing;

use Flex\Core\Http\Request;
use Flex\Core\Http\Response;

final readonly class RouteRunner
{
    public function __construct(
        private ControllerDispatcher $dispatcher,
        private MiddlewarePipeline $pipeline,
        private MiddlewareRegistry $middleware,
    ) {
    }

    public function run(DispatchResult $match, Request $request): Response
    {
        if (!$match->isFound()) {
            throw new \LogicException('Only a found route can be executed.');
        }

        $stack = array_values(array_unique([
            ...$this->middleware->global(),
            ...$match->route()->getMiddleware(),
        ]));

        return $this->pipeline->process(
            $request,
            $stack,
            fn (Request $current): Response => $this->dispatcher->dispatch($match, $current),
        );
    }
}
