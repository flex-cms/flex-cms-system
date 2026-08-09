<?php

declare(strict_types=1);

namespace Flex\Core\Http;

use RuntimeException;

final class ResponseEmitter
{
    public function emit(Response $response): void
    {
        if (headers_sent($file, $line)) {
            throw new RuntimeException("Cannot emit response: headers already sent in {$file}:{$line}.");
        }

        http_response_code($response->status());

        foreach ($response->headers() as $name => $values) {
            foreach ($values as $index => $value) {
                header($this->formatHeaderName($name) . ': ' . $value, $index === 0);
            }
        }

        if (!in_array($response->status(), [204, 304], true)) {
            echo $response->content();
        }
    }

    private function formatHeaderName(string $name): string
    {
        return implode('-', array_map('ucfirst', explode('-', strtolower($name))));
    }
}
