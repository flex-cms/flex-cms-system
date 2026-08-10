<?php

declare(strict_types=1);

namespace Flex\Features\AdminUI\Navigation;

use InvalidArgumentException;

enum SidebarPosition: string
{
    case Left = 'left';
    case Right = 'right';

    public function isLeft(): bool
    {
        return $this === self::Left;
    }

    public function isRight(): bool
    {
        return $this === self::Right;
    }

    public function opposite(): self
    {
        return match ($this) {
            self::Left => self::Right,
            self::Right => self::Left,
        };
    }

    public static function resolve(
        self|string|null $position,
        self $default = self::Left
    ): self {
        if ($position instanceof self) {
            return $position;
        }

        if ($position === null || trim($position) === '') {
            return $default;
        }

        return self::tryFrom(
            strtolower(trim($position))
        ) ?? throw new InvalidArgumentException(
            sprintf(
                'Unsupported sidebar position [%s].',
                $position
            )
        );
    }
}
