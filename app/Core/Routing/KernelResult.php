<?php

declare(strict_types=1);

namespace Flex\Core\Routing;

use Flex\Core\Http\Response;
use LogicException;

final readonly class KernelResult
{
    private function __construct(
        private bool $handled,
        private ?Response $httpResponse = null,
        public ?DispatchResult $match = null,
    ) {
    }

    public static function handled(Response $response, DispatchResult $match): self
    {
        return new self(true, $response, $match);
    }

    public static function pass(DispatchResult $match): self
    {
        return new self(false, match: $match);
    }

    public function isHandled(): bool
    {
        return $this->handled;
    }

    public function shouldPass(): bool
    {
        return !$this->handled;
    }

    public function response(): Response
    {
        return $this->httpResponse
            ?? throw new LogicException('A passed kernel result does not contain a Response.');
    }
}
