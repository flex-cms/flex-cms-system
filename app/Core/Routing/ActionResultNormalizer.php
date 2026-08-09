<?php

declare(strict_types=1);

namespace Flex\Core\Routing;

use Flex\Core\Http\Response;
use Flex\Core\Routing\Exceptions\InvalidRouteResponseException;
use JsonSerializable;
use Stringable;

final class ActionResultNormalizer
{
    public function normalize(mixed $result, string $capturedOutput = ''): Response
    {
        if ($result instanceof Response) {
            if ($capturedOutput !== '') {
                throw new InvalidRouteResponseException(
                    'A route action cannot both output content and return a Response.'
                );
            }

            return $result;
        }

        if ($result === null) {
            return Response::html($capturedOutput);
        }

        if ($capturedOutput !== '') {
            throw new InvalidRouteResponseException(
                'A route action cannot both output content and return a value.'
            );
        }

        if (is_array($result) || $result instanceof JsonSerializable) {
            return Response::json($result);
        }

        if (is_string($result) || $result instanceof Stringable) {
            return Response::html((string) $result);
        }

        if (is_int($result) || is_float($result) || is_bool($result)) {
            return Response::make((string) $result, headers: [
                'Content-Type' => 'text/plain; charset=UTF-8',
            ]);
        }

        throw new InvalidRouteResponseException(sprintf(
            'A route action returned an unsupported value of type [%s].',
            get_debug_type($result),
        ));
    }
}
