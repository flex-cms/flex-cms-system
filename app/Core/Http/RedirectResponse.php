<?php

declare(strict_types=1);

namespace Flex\Core\Http;

use InvalidArgumentException;

final class RedirectResponse extends Response
{
    /** @param array<string, string|string[]> $headers */
    public function __construct(private readonly string $targetUrl, int $status = 302, array $headers = [])
    {
        if (!in_array($status, [201, 301, 302, 303, 307, 308], true)) {
            throw new InvalidArgumentException("Invalid redirect status code: {$status}.");
        }

        if (preg_match('/[\r\n]/', $targetUrl)) {
            throw new InvalidArgumentException('Redirect URL contains invalid characters.');
        }

        parent::__construct('', $status, ['Location' => $targetUrl, ...$headers]);
    }

    public function targetUrl(): string
    {
        return $this->targetUrl;
    }
}
