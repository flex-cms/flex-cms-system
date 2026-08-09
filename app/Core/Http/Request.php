<?php

declare(strict_types=1);

namespace Flex\Core\Http;

use JsonException;

final class Request
{
    /** @var array<string, mixed> */
    private array $attributes = [];

    /** @var array<string, mixed>|null */
    private ?array $decodedJson = null;

    /**
     * @param array<string, mixed> $query
     * @param array<string, mixed> $body
     * @param array<string, string|string[]> $headers
     * @param array<string, mixed> $cookies
     * @param array<string, mixed> $files
     * @param array<string, mixed> $server
     */
    public function __construct(
        private readonly string $method,
        private readonly string $uri,
        private readonly array $query = [],
        private readonly array $body = [],
        private readonly array $headers = [],
        private readonly array $cookies = [],
        private readonly array $files = [],
        private readonly array $server = [],
        private readonly string $rawBody = '',
    ) {
    }

    public static function fromGlobals(): self
    {
        $server = $_SERVER;
        $method = strtoupper((string) ($server['REQUEST_METHOD'] ?? 'GET'));
        $uri = (string) ($server['REQUEST_URI'] ?? '/');

        return new self(
            method: $method,
            uri: $uri,
            query: $_GET,
            body: $_POST,
            headers: self::headersFromServer($server),
            cookies: $_COOKIE,
            files: $_FILES,
            server: $server,
            rawBody: (string) (file_get_contents('php://input') ?: ''),
        );
    }

    public function method(): string
    {
        return strtoupper($this->method);
    }

    public function isMethod(string ...$methods): bool
    {
        $current = $this->method();

        foreach ($methods as $method) {
            if ($current === strtoupper($method)) {
                return true;
            }
        }

        return false;
    }

    public function uri(): string
    {
        return $this->uri;
    }

    public function path(): string
    {
        $path = parse_url($this->uri, PHP_URL_PATH);
        $path = is_string($path) ? rawurldecode($path) : '/';
        $normalized = '/' . trim(preg_replace('#/+#', '/', $path) ?? '/', '/');

        return $normalized === '' ? '/' : $normalized;
    }

    /** @return array<string, mixed> */
    public function queryAll(): array
    {
        return $this->query;
    }

    public function query(?string $key = null, mixed $default = null): mixed
    {
        return $key === null ? $this->query : self::dataGet($this->query, $key, $default);
    }

    /** @return array<string, mixed> */
    public function bodyAll(): array
    {
        return $this->body;
    }

    public function input(?string $key = null, mixed $default = null): mixed
    {
        $input = array_replace_recursive($this->query, $this->body, $this->json());

        return $key === null ? $input : self::dataGet($input, $key, $default);
    }

    /** @param string[] $keys */
    public function only(array $keys): array
    {
        $result = [];

        foreach ($keys as $key) {
            $value = $this->input($key, self::missing());
            if ($value !== self::missing()) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /** @param string[] $keys */
    public function except(array $keys): array
    {
        return array_diff_key($this->input(), array_flip($keys));
    }

    public function string(string $key, string $default = ''): string
    {
        $value = $this->input($key, $default);

        return is_scalar($value) || $value instanceof \Stringable ? (string) $value : $default;
    }

    public function integer(string $key, int $default = 0): int
    {
        $value = filter_var($this->input($key), FILTER_VALIDATE_INT);

        return $value === false ? $default : $value;
    }

    public function boolean(string $key, bool $default = false): bool
    {
        $value = $this->input($key, null);
        if ($value === null) {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $default;
    }

    /** @return array<string, mixed> */
    public function json(): array
    {
        if ($this->decodedJson !== null) {
            return $this->decodedJson;
        }

        if ($this->rawBody === '' || !$this->isJson()) {
            return $this->decodedJson = [];
        }

        try {
            $decoded = json_decode($this->rawBody, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return $this->decodedJson = [];
        }

        return $this->decodedJson = is_array($decoded) ? $decoded : [];
    }

    public function rawBody(): string
    {
        return $this->rawBody;
    }

    public function header(string $name, string|array|null $default = null): string|array|null
    {
        return $this->headers[strtolower($name)] ?? $default;
    }

    /** @return array<string, string|string[]> */
    public function headers(): array
    {
        return $this->headers;
    }

    public function cookie(string $key, mixed $default = null): mixed
    {
        return self::dataGet($this->cookies, $key, $default);
    }

    public function file(string $key, mixed $default = null): mixed
    {
        return self::dataGet($this->files, $key, $default);
    }

    public function server(string $key, mixed $default = null): mixed
    {
        return $this->server[$key] ?? $default;
    }

    public function ip(): ?string
    {
        $ip = $this->server('REMOTE_ADDR');

        return is_string($ip) && filter_var($ip, FILTER_VALIDATE_IP) ? $ip : null;
    }

    public function isJson(): bool
    {
        return str_contains(strtolower((string) $this->header('Content-Type', '')), 'application/json');
    }

    public function expectsJson(): bool
    {
        return str_starts_with($this->path(), '/api/')
            || str_contains(strtolower((string) $this->header('Accept', '')), 'application/json')
            || $this->isAjax();
    }

    public function isAjax(): bool
    {
        return strtolower((string) $this->header('X-Requested-With', '')) === 'xmlhttprequest';
    }

    public function withAttribute(string $key, mixed $value): self
    {
        $clone = clone $this;
        $clone->attributes[$key] = $value;

        return $clone;
    }

    /** @param array<string, mixed> $attributes */
    public function withAttributes(array $attributes): self
    {
        $clone = clone $this;
        $clone->attributes = array_replace($clone->attributes, $attributes);

        return $clone;
    }

    public function attribute(string $key, mixed $default = null): mixed
    {
        return self::dataGet($this->attributes, $key, $default);
    }

    /** @return array<string, mixed> */
    public function attributes(): array
    {
        return $this->attributes;
    }

    public function route(?string $key = null, mixed $default = null): mixed
    {
        $parameters = $this->attribute('_route_parameters', []);

        return $key === null ? $parameters : self::dataGet($parameters, $key, $default);
    }

    /** @param array<string, mixed> $server */
    private static function headersFromServer(array $server): array
    {
        $headers = [];

        foreach ($server as $key => $value) {
            if (!is_string($value)) {
                continue;
            }

            if (str_starts_with($key, 'HTTP_')) {
                $name = str_replace('_', '-', strtolower(substr($key, 5)));
                $headers[$name] = $value;
            }
        }

        foreach (['CONTENT_TYPE' => 'content-type', 'CONTENT_LENGTH' => 'content-length'] as $key => $name) {
            if (isset($server[$key]) && is_string($server[$key])) {
                $headers[$name] = $server[$key];
            }
        }

        return $headers;
    }

    /** @param array<string, mixed> $data */
    private static function dataGet(array $data, string $key, mixed $default): mixed
    {
        if (array_key_exists($key, $data)) {
            return $data[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (!is_array($data) || !array_key_exists($segment, $data)) {
                return $default;
            }
            $data = $data[$segment];
        }

        return $data;
    }

    private static function missing(): object
    {
        static $missing;

        return $missing ??= new \stdClass();
    }
}
