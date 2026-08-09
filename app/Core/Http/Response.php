<?php

declare(strict_types=1);

namespace Flex\Core\Http;

use InvalidArgumentException;

class Response
{
    /** @var array<string, string[]> */
    private array $headers = [];

    /** @param array<string, string|string[]> $headers */
    public function __construct(
        private string $content = '',
        private int $status = 200,
        array $headers = [],
    ) {
        $this->assertValidStatus($status);

        foreach ($headers as $name => $values) {
            $this->headers[strtolower($name)] = array_values((array) $values);
        }
    }

    /** @param array<string, string|string[]> $headers */
    public static function make(string $content = '', int $status = 200, array $headers = []): static
    {
        return new static($content, $status, $headers);
    }

    /** @param array<string, string|string[]> $headers */
    public static function html(string $content, int $status = 200, array $headers = []): static
    {
        return new static($content, $status, ['Content-Type' => 'text/html; charset=UTF-8', ...$headers]);
    }

    /** @param array<string, string|string[]> $headers */
    public static function json(mixed $data, int $status = 200, array $headers = []): JsonResponse
    {
        return new JsonResponse($data, $status, $headers);
    }

    /** @param array<string, string|string[]> $headers */
    public static function redirect(string $url, int $status = 302, array $headers = []): RedirectResponse
    {
        return new RedirectResponse($url, $status, $headers);
    }

    public static function noContent(int $status = 204): static
    {
        return new static('', $status);
    }

    public function content(): string
    {
        return $this->content;
    }

    public function status(): int
    {
        return $this->status;
    }

    /** @return array<string, string[]> */
    public function headers(): array
    {
        return $this->headers;
    }

    public function header(string $name): ?string
    {
        return $this->headers[strtolower($name)][0] ?? null;
    }

    public function withContent(string $content): static
    {
        $clone = clone $this;
        $clone->content = $content;

        return $clone;
    }

    public function withStatus(int $status): static
    {
        $this->assertValidStatus($status);
        $clone = clone $this;
        $clone->status = $status;

        return $clone;
    }

    public function withHeader(string $name, string|array $value): static
    {
        $clone = clone $this;
        $clone->headers[strtolower($name)] = array_values((array) $value);

        return $clone;
    }

    public function withAddedHeader(string $name, string $value): static
    {
        $clone = clone $this;
        $key = strtolower($name);
        $clone->headers[$key][] = $value;

        return $clone;
    }

    public function withoutHeader(string $name): static
    {
        $clone = clone $this;
        unset($clone->headers[strtolower($name)]);

        return $clone;
    }

    private function assertValidStatus(int $status): void
    {
        if ($status < 100 || $status > 599) {
            throw new InvalidArgumentException("Invalid HTTP status code: {$status}.");
        }
    }
}
