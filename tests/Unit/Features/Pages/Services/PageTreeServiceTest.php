<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Pages\Services;

use Flex\Features\Pages\Exceptions\DuplicatePageSlugException;
use Flex\Features\Pages\Exceptions\InvalidPageParentException;
use Flex\Features\Pages\Exceptions\InvalidPageSlugException;
use Flex\Features\Pages\Models\Page;
use Flex\Features\Pages\Repositories\PageRepositoryInterface;
use Flex\Features\Pages\Services\PageTreeService;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class PageTreeServiceTest extends TestCase
{
    private PageRepositoryInterface&MockObject $repository;
    private PageTreeService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(
            PageRepositoryInterface::class
        );
        $this->service = new PageTreeService($this->repository);
    }

    public function testItBuildsRootPathDataFromName(): void
    {
        $this->repository
            ->expects(self::once())
            ->method('slugExists')
            ->with('about-us', null, null)
            ->willReturn(false);

        self::assertSame([
            'slug' => 'about-us',
            'full_slug' => 'about-us',
            'parent_id' => null,
        ], $this->service->pathData('About us'));
    }

    public function testItBuildsNestedFullSlug(): void
    {
        $parent = $this->page(10, 'Services', 'services', 'services');

        $this->repository
            ->expects(self::once())
            ->method('find')
            ->with(10)
            ->willReturn($parent);
        $this->repository
            ->expects(self::once())
            ->method('slugExists')
            ->with('web-development', 10, null)
            ->willReturn(false);

        self::assertSame([
            'slug' => 'web-development',
            'full_slug' => 'services/web-development',
            'parent_id' => 10,
        ], $this->service->pathData(
            'Web development',
            parentId: 10
        ));
    }

    public function testItNormalizesExplicitSlug(): void
    {
        $this->repository
            ->method('slugExists')
            ->willReturn(false);

        $data = $this->service->pathData(
            'Ignored name',
            '  Custom URL  '
        );

        self::assertSame('custom-url', $data['slug']);
    }

    public function testItRejectsEmptySlug(): void
    {
        $this->repository
            ->expects(self::never())
            ->method('slugExists');

        $this->expectException(InvalidPageSlugException::class);

        $this->service->pathData('---');
    }

    public function testItRejectsDuplicateSiblingSlug(): void
    {
        $this->repository
            ->expects(self::once())
            ->method('slugExists')
            ->with('about', null, 7)
            ->willReturn(true);

        $this->expectException(DuplicatePageSlugException::class);

        $this->service->pathData(
            'About',
            currentPage: $this->page(7, 'Old', 'old', 'old')
        );
    }

    public function testItRejectsMissingParent(): void
    {
        $this->repository
            ->expects(self::once())
            ->method('find')
            ->with(99)
            ->willReturn(null);

        $this->expectException(InvalidPageParentException::class);

        $this->service->pathData('Child', parentId: 99);
    }

    public function testItRejectsMovingPageBelowItself(): void
    {
        $page = $this->page(7, 'About', 'about', 'about');

        $this->repository
            ->expects(self::once())
            ->method('find')
            ->with(7)
            ->willReturn($page);

        $this->expectException(InvalidPageParentException::class);

        $this->service->pathData(
            'About',
            parentId: 7,
            currentPage: $page
        );
    }

    public function testItRejectsMovingPageBelowDescendant(): void
    {
        $page = $this->page(1, 'Root', 'root', 'root');
        $descendant = $this->page(
            3,
            'Grandchild',
            'grandchild',
            'root/child/grandchild',
            1
        );

        $this->repository
            ->expects(self::exactly(2))
            ->method('find')
            ->willReturnCallback(
                static fn (int $id): ?Page => match ($id) {
                    3 => $descendant,
                    1 => $page,
                    default => null,
                }
            );

        $this->expectException(InvalidPageParentException::class);

        $this->service->pathData(
            'Root',
            parentId: 3,
            currentPage: $page
        );
    }

    public function testItFlattensAndSortsPageForest(): void
    {
        $root = $this->page(1, 'Root', 'root', 'root', null, 2);
        $firstRoot = $this->page(2, 'First', 'first', 'first', null, 1);
        $lastChild = $this->page(3, 'Last child', 'last', 'root/last', 1, 2);
        $firstChild = $this->page(4, 'First child', 'first', 'root/first', 1, 1);
        $orphan = $this->page(5, 'Orphan', 'orphan', 'orphan', 999, 3);

        $items = $this->service->flatten([
            $lastChild,
            $root,
            $orphan,
            $firstChild,
            $firstRoot,
        ]);

        self::assertSame(
            [2, 1, 4, 3, 5],
            array_map(
                static fn ($item): int => (int) $item->page->id,
                $items
            )
        );
        self::assertSame([0, 0, 1, 1, 0], array_map(
            static fn ($item): int => $item->level,
            $items
        ));
        self::assertSame('— First child', $items[2]->displayName());
    }

    public function testItSynchronizesAllDescendantPathsInTransaction(): void
    {
        $root = $this->page(1, 'Root', 'root', 'new-root');
        $child = $this->page(2, 'Child', 'child', 'old/child', 1);
        $grandchild = $this->page(
            3,
            'Grandchild',
            'grandchild',
            'old/child/grandchild',
            2
        );

        $this->repository
            ->expects(self::once())
            ->method('transaction')
            ->willReturnCallback(
                static fn (callable $callback): mixed => $callback()
            );
        $this->repository
            ->expects(self::once())
            ->method('all')
            ->willReturn(new Collection([$root, $child, $grandchild]));

        $updates = [];
        $this->repository
            ->expects(self::exactly(2))
            ->method('update')
            ->willReturnCallback(
                static function (Page $page, array $data) use (&$updates): Page {
                    $updates[(int) $page->id] = $data['full_slug'];
                    $page->full_slug = $data['full_slug'];

                    return $page;
                }
            );

        $this->service->syncDescendantPaths($root);

        self::assertSame([
            2 => 'new-root/child',
            3 => 'new-root/child/grandchild',
        ], $updates);
    }

    private function page(
        int $id,
        string $name,
        string $slug,
        string $fullSlug,
        ?int $parentId = null,
        int $position = 0
    ): Page {
        $page = new Page();
        $page->setRawAttributes([
            'id' => $id,
            'name' => $name,
            'slug' => $slug,
            'full_slug' => $fullSlug,
            'parent_id' => $parentId,
            'position' => $position,
            'is_active' => true,
        ], true);
        $page->exists = true;

        return $page;
    }
}
