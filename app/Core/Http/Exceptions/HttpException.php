<?php

declare(strict_types=1);

namespace Flex\Core\Http\Exceptions;

use RuntimeException;
use Throwable;

class HttpException extends RuntimeException
{
    /** @param array<string, string|string[]> $headers */
    public function __construct(
        private readonly int $statusCode,
        string $message = '',
        private readonly array $headers = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $previous);
    }

    public function statusCode(): int { return $this->statusCode; }
    public function headers(): array { return $this->headers; }
}
