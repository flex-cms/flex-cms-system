<?php

declare(strict_types=1);

namespace Flex\Core\Http;

use JsonException;

final class JsonResponse extends Response
{
    /** @param array<string, string|string[]> $headers */
    public function __construct(mixed $data, int $status = 200, array $headers = [])
    {
        try {
            $json = json_encode(
                $data,
                JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );
        } catch (JsonException $exception) {
            throw new JsonException('Unable to encode the JSON response.', previous: $exception);
        }

        parent::__construct($json, $status, [
            'Content-Type' => 'application/json; charset=UTF-8',
            ...$headers,
        ]);
    }
}
