<?php

declare(strict_types=1);

namespace Flex\Core\Routing;

use Flex\Core\Http\Contracts\ExceptionHandlerInterface;
use Flex\Core\Http\Request;
use Flex\Core\Http\Response;
use Throwable;

final readonly class FlexRouterKernel
{
    public function __construct(
        private RouteMatcher $matcher,
        private RouteRunner $runner,
        private ExceptionHandlerInterface $exceptions,
        private bool $passNotFound = true,
    ) {
    }

    public function handle(Request $request): KernelResult
    {
        try {
            return $this->dispatch($request);
        } catch (Throwable $exception) {
            return KernelResult::handled(
                $this->exceptions->render($request, $exception),
                DispatchResult::notFound(),
            );
        }
    }

    private function dispatch(Request $request): KernelResult
    {
        $match = $this->matcher->match($request);
        if ($match->isFound()) {
            $response = $this->runner->run($match, $request);
            if ($request->isMethod('HEAD')) { $response = $response->withContent(''); }
            return KernelResult::handled($response, $match);
        }
        if ($match->isMethodNotAllowed()) {
            return KernelResult::handled($this->errorResponse($request, 405, 'Method Not Allowed', [
                'Allow' => implode(', ', $match->allowedMethods()),
            ]), $match);
        }
        if ($this->passNotFound) { return KernelResult::pass($match); }
        return KernelResult::handled($this->errorResponse($request, 404, 'Not Found'), $match);
    }

    private function errorResponse(Request $request, int $status, string $message, array $headers = []): Response
    {
        return $request->expectsJson()
            ? Response::json(['status' => 'error', 'message' => $message], $status, $headers)
            : Response::html(sprintf('<h1>%d - %s</h1>', $status, htmlspecialchars($message, ENT_QUOTES, 'UTF-8')), $status, $headers);
    }
}
