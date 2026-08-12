<?php

declare(strict_types=1);

namespace Flex\Core\Helpers;

final class Flash
{
    private const SESSION_KEY = '_flex_flash';

    public static function success(string $message): void
    {
        self::add('success', $message);
    }

    public static function error(string $message): void
    {
        self::add('error', $message);
    }

    public static function warning(string $message): void
    {
        self::add('warning', $message);
    }

    public static function info(string $message): void
    {
        self::add('info', $message);
    }

    public static function add(
        string $type,
        string $message
    ): void {
        $_SESSION[self::SESSION_KEY][] = [
            'type' => $type,
            'message' => $message,
        ];
    }

    public static function pull(): array
    {
        $messages = $_SESSION[self::SESSION_KEY] ?? [];

        unset($_SESSION[self::SESSION_KEY]);

        return is_array($messages)
            ? $messages
            : [];
    }

    public static function has(): bool
    {
        return !empty(
            $_SESSION[self::SESSION_KEY] ?? []
        );
    }
}
