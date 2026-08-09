<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Settings\Exceptions;

use Flex\Features\Settings\Exceptions\UnknownSettingsGroupException;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class UnknownSettingsGroupExceptionTest extends TestCase
{
    public function testItContainsTheUnknownGroup(): void
    {
        $exception = new UnknownSettingsGroupException('unknown');

        self::assertSame('unknown', $exception->group());

        self::assertSame(
            'Unknown settings page group [unknown].',
            $exception->getMessage()
        );
    }

    public function testItIsAnInvalidArgumentException(): void
    {
        $exception = new UnknownSettingsGroupException('unknown');

        self::assertInstanceOf(
            InvalidArgumentException::class,
            $exception
        );
    }
}