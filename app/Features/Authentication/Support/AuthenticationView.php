<?php

declare(strict_types=1);

namespace Flex\Features\Authentication\Support;

use JsonException;

final class AuthenticationView
{
    /**
     * @throws JsonException
     */
    public static function json(
        mixed $value
    ): string {
        return json_encode(
            $value,
            JSON_THROW_ON_ERROR
            | JSON_HEX_TAG
            | JSON_HEX_AMP
            | JSON_HEX_APOS
            | JSON_HEX_QUOT
            | JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
        );
    }
}
