<?php

declare(strict_types=1);

namespace Flex\Core\Features\Migrations\Support;

use InvalidArgumentException;

final class MigrationName
{
    public static function normalize(string $name): string
    {
        $name = trim($name);

        if ($name === '') {
            throw new InvalidArgumentException(
                'Името на migration не може да бъде празно.'
            );
        }

        $name = preg_replace(
            '/(?<!^)[A-Z]/',
            '_$0',
            $name
        ) ?? $name;

        $name = strtolower($name);
        $name = preg_replace(
            '/[^a-z0-9]+/',
            '_',
            $name
        ) ?? $name;

        $name = trim($name, '_');

        if ($name === '') {
            throw new InvalidArgumentException(
                'Името на migration е невалидно.'
            );
        }

        return $name;
    }

    public static function className(string $name): string
    {
        return str_replace(
            ' ',
            '',
            ucwords(
                str_replace(
                    '_',
                    ' ',
                    self::normalize($name)
                )
            )
        );
    }
}
