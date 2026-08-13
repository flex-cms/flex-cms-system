<?php

declare(strict_types=1);

namespace Flex\Features\Pages\Support;

final class PagesTables
{
    private const PREFIX = 'pages_';

    public static function pages(): string
    {
        return self::PREFIX . 'pages';
    }

    public static function options(): string
    {
        return self::PREFIX . 'options';
    }

    public static function elements(): string
    {
        return self::PREFIX . 'elements';
    }
}
