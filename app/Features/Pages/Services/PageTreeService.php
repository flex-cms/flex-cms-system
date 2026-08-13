<?php

declare(strict_types=1);

namespace Flex\Features\Pages\Services;

use Flex\Core\Support\Slug;
use Flex\Features\Pages\Data\PageTreeItem;
use Flex\Features\Pages\Exceptions\DuplicatePageSlugException;
use Flex\Features\Pages\Exceptions\InvalidPageParentException;
use Flex\Features\Pages\Exceptions\InvalidPageSlugException;
use Flex\Features\Pages\Models\Page;
use Flex\Features\Pages\Repositories\PageRepositoryInterface;

final readonly class PageTreeService
{
    private const MAX_SLUG_LENGTH = 255;
    private const MAX_FULL_SLUG_LENGTH = 512;

    public function __construct(
        private PageRepositoryInterface $pages
    ) {
    }

    /**
     * @return array{slug: string, full_slug: string, parent_id: int|null}
     */
    public function pathData(
        string $name,
        ?string $requestedSlug = null,
        ?int $parentId = null,
        ?Page $currentPage = null
    ): array {
        $parent = $this->validateParent($parentId, $currentPage);
        $slug = $this->normalizeSlug($requestedSlug ?: $name);

        if ($this->pages->slugExists(
            $slug,
            $parent?->id,
            $currentPage?->id
        )) {
            throw new DuplicatePageSlugException($slug);
        }

        $fullSlug = $parent === null
            ? $slug
            : $parent->full_slug . '/' . $slug;

        $this->assertValidFullSlug($fullSlug);

        return [
            'slug' => $slug,
            'full_slug' => $fullSlug,
            'parent_id' => $parent?->id,
        ];
    }

    public function normalizeSlug(string $value): string
    {
        $slug = Slug::make($value);

        if ($slug === '') {
            throw new InvalidPageSlugException(
                'A page slug cannot be empty.'
            );
        }

        if (strlen($slug) > self::MAX_SLUG_LENGTH) {
            throw new InvalidPageSlugException(
                sprintf(
                    'A page slug cannot exceed %d characters.',
                    self::MAX_SLUG_LENGTH
                )
            );
        }

        return $slug;
    }

    /** @return list<PageTreeItem> */
    public function flatten(iterable $pages): array
    {
        $byId = [];

        foreach ($pages as $page) {
            if ($page instanceof Page && $page->id !== null) {
                $byId[(int) $page->id] = $page;
            }
        }

        $children = [];
        $roots = [];

        foreach ($byId as $id => $page) {
            $parentId = $page->parent_id !== null
                ? (int) $page->parent_id
                : null;

            if ($parentId === null || !isset($byId[$parentId])) {
                $roots[] = $page;
                continue;
            }

            $children[$parentId][] = $page;
        }

        $this->sortPages($roots);

        foreach ($children as &$siblings) {
            $this->sortPages($siblings);
        }

        unset($siblings);

        $result = [];
        $visited = [];

        foreach ($roots as $root) {
            $this->flattenBranch(
                $root,
                $children,
                0,
                $visited,
                $result
            );
        }

        return $result;
    }

    public function syncDescendantPaths(Page $page): void
    {
        $this->pages->transaction(
            function () use ($page): void {
                $allPages = $this->pages->all();
                $children = [];

                foreach ($allPages as $candidate) {
                    if ($candidate->parent_id !== null) {
                        $children[(int) $candidate->parent_id][] = $candidate;
                    }
                }

                $visited = [(int) $page->id => true];
                $this->syncChildren($page, $children, $visited);
            }
        );
    }

    private function validateParent(
        ?int $parentId,
        ?Page $currentPage
    ): ?Page {
        if ($parentId === null) {
            return null;
        }

        $parent = $this->pages->find($parentId);

        if ($parent === null || $parent->trashed()) {
            throw new InvalidPageParentException(
                sprintf('Parent page [%d] does not exist.', $parentId)
            );
        }

        if ($currentPage === null) {
            return $parent;
        }

        $visited = [];
        $candidate = $parent;

        while ($candidate !== null) {
            $candidateId = (int) $candidate->id;

            if ($candidateId === (int) $currentPage->id) {
                throw new InvalidPageParentException(
                    'A page cannot be moved below itself or one of its descendants.'
                );
            }

            if (isset($visited[$candidateId])) {
                throw new InvalidPageParentException(
                    'The selected parent belongs to a cyclic page hierarchy.'
                );
            }

            $visited[$candidateId] = true;
            $candidate = $candidate->parent_id !== null
                ? $this->pages->find((int) $candidate->parent_id)
                : null;
        }

        return $parent;
    }

    private function assertValidFullSlug(string $fullSlug): void
    {
        if (strlen($fullSlug) > self::MAX_FULL_SLUG_LENGTH) {
            throw new InvalidPageSlugException(
                sprintf(
                    'A full page slug cannot exceed %d characters.',
                    self::MAX_FULL_SLUG_LENGTH
                )
            );
        }
    }

    /** @param list<Page> $pages */
    private function sortPages(array &$pages): void
    {
        usort(
            $pages,
            static fn (Page $left, Page $right): int => [
                (int) $left->position,
                (int) $left->id,
            ] <=> [
                (int) $right->position,
                (int) $right->id,
            ]
        );
    }

    /**
     * @param array<int, list<Page>> $children
     * @param array<int, true> $visited
     * @param list<PageTreeItem> $result
     */
    private function flattenBranch(
        Page $page,
        array $children,
        int $level,
        array &$visited,
        array &$result
    ): void {
        $pageId = (int) $page->id;

        if (isset($visited[$pageId])) {
            return;
        }

        $visited[$pageId] = true;
        $result[] = new PageTreeItem($page, $level);

        foreach ($children[$pageId] ?? [] as $child) {
            $this->flattenBranch(
                $child,
                $children,
                $level + 1,
                $visited,
                $result
            );
        }
    }

    /**
     * @param array<int, list<Page>> $children
     * @param array<int, true> $visited
     */
    private function syncChildren(
        Page $parent,
        array $children,
        array &$visited
    ): void {
        foreach ($children[(int) $parent->id] ?? [] as $child) {
            $childId = (int) $child->id;

            if (isset($visited[$childId])) {
                throw new InvalidPageParentException(
                    'A cyclic page hierarchy cannot be synchronized.'
                );
            }

            $visited[$childId] = true;
            $fullSlug = $parent->full_slug . '/' . $child->slug;
            $this->assertValidFullSlug($fullSlug);

            $child = $this->pages->update($child, [
                'full_slug' => $fullSlug,
            ]);

            $this->syncChildren($child, $children, $visited);
        }
    }
}
