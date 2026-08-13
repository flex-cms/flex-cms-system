<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Pages\Navigation;

use Flex\Features\AdminUI\Navigation\DefaultAdminNavigation;
use Flex\Features\AdminUI\Navigation\SidebarRegistry;
use Flex\Features\Pages\Navigation\PagesNavigation;
use LogicException;
use PHPUnit\Framework\TestCase;

final class PagesNavigationTest extends TestCase
{
    public function testItRegistersPagesNavigationItem(): void
    {
        $registry = $this->registry();

        $item = (new PagesNavigation($registry))->register();
        $data = $item->toArray();

        self::assertSame('pages', $data['id']);
        self::assertSame('Страници', $data['label']);
        self::assertSame('/admin/pages', $data['url']);
        self::assertSame('fa-solid fa-file-lines', $data['icon']);
        self::assertSame(10, $data['priority']);
        self::assertTrue($data['exact']);
        self::assertTrue($data['turbo']);
        self::assertTrue($registry->sidebar()->has('pages'));
    }

    public function testItDoesNotRegisterDuplicateItem(): void
    {
        $registry = $this->registry();
        $navigation = new PagesNavigation($registry);

        $first = $navigation->register();
        $second = $navigation->register();

        self::assertSame($first, $second);
        self::assertCount(1, $registry->sidebar()->toArray()['items']);
    }

    public function testItRequiresDefaultSidebar(): void
    {
        $this->expectException(LogicException::class);

        (new PagesNavigation(new SidebarRegistry()))->register();
    }

    private function registry(): SidebarRegistry
    {
        $registry = new SidebarRegistry();
        (new DefaultAdminNavigation($registry))->register();

        return $registry;
    }
}
