<?php

declare(strict_types=1);

namespace Tests\Unit\Features\AdminUI\Navigation;

use Flex\Features\AdminUI\Navigation\SidebarPosition;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class SidebarPositionTest extends TestCase
{
    public function testItIdentifiesLeftPosition(): void
    {
        self::assertTrue(
            SidebarPosition::Left->isLeft()
        );

        self::assertFalse(
            SidebarPosition::Left->isRight()
        );
    }

    public function testItIdentifiesRightPosition(): void
    {
        self::assertTrue(
            SidebarPosition::Right->isRight()
        );

        self::assertFalse(
            SidebarPosition::Right->isLeft()
        );
    }

    public function testItReturnsOppositePosition(): void
    {
        self::assertSame(
            SidebarPosition::Right,
            SidebarPosition::Left->opposite()
        );

        self::assertSame(
            SidebarPosition::Left,
            SidebarPosition::Right->opposite()
        );
    }

    public function testItResolvesStringPosition(): void
    {
        self::assertSame(
            SidebarPosition::Left,
            SidebarPosition::resolve('left')
        );

        self::assertSame(
            SidebarPosition::Right,
            SidebarPosition::resolve(
                ' RIGHT '
            )
        );
    }

    public function testItPreservesEnumPosition(): void
    {
        self::assertSame(
            SidebarPosition::Right,
            SidebarPosition::resolve(
                SidebarPosition::Right
            )
        );
    }

    public function testItReturnsDefaultPosition(): void
    {
        self::assertSame(
            SidebarPosition::Left,
            SidebarPosition::resolve(null)
        );

        self::assertSame(
            SidebarPosition::Right,
            SidebarPosition::resolve(
                '',
                SidebarPosition::Right
            )
        );
    }

    public function testItRejectsUnsupportedPosition(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Unsupported sidebar position [top].'
        );

        SidebarPosition::resolve('top');
    }
}
