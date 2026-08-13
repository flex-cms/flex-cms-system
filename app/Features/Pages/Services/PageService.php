<?php

declare(strict_types=1);

namespace Flex\Features\Pages\Services;

use Flex\Features\Pages\Data\PageTreeItem;
use Flex\Features\Pages\Exceptions\InvalidPageDataException;
use Flex\Features\Pages\Models\Page;
use Flex\Features\Pages\Repositories\PageRepositoryInterface;

final readonly class PageService
{
    private const MAX_NAME_LENGTH = 255;

    public function __construct(
        private PageRepositoryInterface $pages,
        private PageTreeService $tree
    ) {
    }

    /** @return list<PageTreeItem> */
    public function tree(
        ?string $search = null,
        ?string $status = null
    ): array {
        return $this->tree->flatten(
            $this->pages->all($search, $status)
        );
    }

    public function findOrFail(int $id): Page
    {
        return $this->pages->findOrFail($id);
    }

    /**
     * @param array<string, mixed> $input
     * @return array{data: list<array<string, mixed>>, pagination: array<string, int>}
     */
    public function paginate(array $input): array
    {
        $page = max(1, (int) ($input['page'] ?? 1));
        $perPage = (int) ($input['per_page'] ?? 25);

        if (!in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 25;
        }

        $sortBy = isset($input['sort'])
            ? trim((string) $input['sort'])
            : null;
        $direction = isset($input['direction'])
            ? strtolower(trim((string) $input['direction']))
            : null;

        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = null;
        }

        return $this->pages->paginate(
            page: $page,
            perPage: $perPage,
            sortBy: $sortBy,
            sortDirection: $direction,
            search: isset($input['search'])
                ? trim((string) $input['search'])
                : null,
            filters: isset($input['filter']) && is_array($input['filter'])
                ? $input['filter']
                : []
        );
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Page
    {
        $name = $this->name($data['name'] ?? null);
        $parentId = $this->parentId($data['parent_id'] ?? null);
        $requestedSlug = $this->optionalString(
            $data['slug'] ?? null,
            'slug'
        );
        $position = $this->position($data['position'] ?? 0);
        $active = $this->boolean($data['is_active'] ?? true, 'is_active');

        return $this->pages->transaction(
            function () use (
                $name,
                $requestedSlug,
                $parentId,
                $position,
                $active
            ): Page {
                $path = $this->tree->pathData(
                    $name,
                    $requestedSlug,
                    $parentId
                );

                return $this->pages->create([
                    'name' => $name,
                    ...$path,
                    'position' => $position,
                    'is_active' => $active,
                ]);
            }
        );
    }

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): Page
    {
        return $this->pages->transaction(
            function () use ($id, $data): Page {
                $page = $this->pages->findOrFail($id);
                $name = array_key_exists('name', $data)
                    ? $this->name($data['name'])
                    : $page->name;
                $requestedSlug = array_key_exists('slug', $data)
                    ? $this->optionalString($data['slug'], 'slug')
                    : $page->slug;
                $parentId = array_key_exists('parent_id', $data)
                    ? $this->parentId($data['parent_id'])
                    : $page->parent_id;

                $path = $this->tree->pathData(
                    $name,
                    $requestedSlug,
                    $parentId,
                    $page
                );

                $updates = [
                    'name' => $name,
                    ...$path,
                ];

                if (array_key_exists('position', $data)) {
                    $updates['position'] = $this->position($data['position']);
                }

                if (array_key_exists('is_active', $data)) {
                    $updates['is_active'] = $this->boolean(
                        $data['is_active'],
                        'is_active'
                    );
                }

                $oldFullSlug = $page->full_slug;
                $page = $this->pages->update($page, $updates);

                if ($page->full_slug !== $oldFullSlug) {
                    $this->tree->syncDescendantPaths($page);
                }

                return $page;
            }
        );
    }

    public function delete(int $id): void
    {
        $this->pages->transaction(
            function () use ($id): void {
                $page = $this->pages->findOrFail($id);

                if (!$page->trashed()) {
                    $this->pages->setActive($page, false);
                    $this->pages->delete($page);
                }
            }
        );
    }

    public function restore(int $id): Page
    {
        return $this->pages->transaction(
            function () use ($id): Page {
                $page = $this->pages->findOrFail($id);

                if ($page->trashed()) {
                    $this->pages->restore($page);
                }

                return $this->pages->setActive($page, false);
            }
        );
    }

    public function forceDelete(int $id): void
    {
        $this->pages->transaction(
            function () use ($id): void {
                $page = $this->pages->findOrFail($id);
                $children = $this->pages->all()
                    ->filter(
                        static fn (Page $candidate): bool =>
                            (int) $candidate->parent_id === $id
                    )
                    ->values();

                $this->pages->forceDelete($page);

                foreach ($children as $child) {
                    $child = $this->pages->update($child, [
                        'parent_id' => null,
                        'full_slug' => $child->slug,
                    ]);

                    $this->tree->syncDescendantPaths($child);
                }
            }
        );
    }

    public function setActive(int $id, bool $active): Page
    {
        $page = $this->pages->findOrFail($id);

        if ($page->trashed()) {
            throw new InvalidPageDataException(
                'A deleted page cannot be activated or deactivated.'
            );
        }

        return $this->pages->setActive($page, $active);
    }

    public function toggle(int $id): Page
    {
        $page = $this->pages->findOrFail($id);

        if ($page->trashed()) {
            throw new InvalidPageDataException(
                'A deleted page cannot be toggled.'
            );
        }

        return $this->pages->setActive(
            $page,
            !$page->is_active
        );
    }

    /**
     * @param array<string, mixed> $input
     * @return array{affected: int, message: string}
     */
    public function bulk(array $input): array
    {
        $action = trim((string) ($input['action'] ?? ''));
        $ids = $this->bulkIds($input['ids'] ?? []);

        if ($ids === []) {
            throw new InvalidPageDataException(
                'Не са избрани валидни страници.'
            );
        }

        if (!in_array($action, [
            'activate',
            'deactivate',
            'trash',
            'restore',
            'force-delete',
        ], true)) {
            throw new InvalidPageDataException(
                'Невалидно групово действие.'
            );
        }

        $affected = 0;

        foreach ($ids as $id) {
            match ($action) {
                'activate' => $this->setActive($id, true),
                'deactivate' => $this->setActive($id, false),
                'trash' => $this->delete($id),
                'restore' => $this->restore($id),
                'force-delete' => $this->forceDelete($id),
            };

            $affected++;
        }

        $label = match ($action) {
            'activate' => 'Активирани страници',
            'deactivate' => 'Деактивирани страници',
            'trash' => 'Преместени в кошчето страници',
            'restore' => 'Възстановени страници',
            'force-delete' => 'Изтрити завинаги страници',
        };

        return [
            'affected' => $affected,
            'message' => sprintf('%s: %d.', $label, $affected),
        ];
    }

    /** @param list<array{id: mixed, position: mixed}> $positions */
    public function reorder(array $positions): void
    {
        $normalized = [];
        $seen = [];

        foreach ($positions as $item) {
            if (!is_array($item)) {
                throw new InvalidPageDataException(
                    'Each page position must be an array.'
                );
            }

            $id = $this->positiveInteger($item['id'] ?? null, 'id');

            if (isset($seen[$id])) {
                throw new InvalidPageDataException(
                    sprintf('Page [%d] occurs more than once in reorder data.', $id)
                );
            }

            $seen[$id] = true;
            $normalized[] = [
                'id' => $id,
                'position' => $this->position($item['position'] ?? null),
            ];
        }

        if ($normalized === []) {
            return;
        }

        $this->pages->transaction(
            function () use ($normalized): void {
                $this->pages->updatePositions($normalized);
            }
        );
    }

    private function name(mixed $value): string
    {
        if (!is_string($value)) {
            throw new InvalidPageDataException(
                'A page name must be a string.'
            );
        }

        $name = trim($value);

        if ($name === '') {
            throw new InvalidPageDataException(
                'A page name cannot be empty.'
            );
        }

        if (mb_strlen($name, 'UTF-8') > self::MAX_NAME_LENGTH) {
            throw new InvalidPageDataException(
                sprintf(
                    'A page name cannot exceed %d characters.',
                    self::MAX_NAME_LENGTH
                )
            );
        }

        return $name;
    }

    private function optionalString(mixed $value, string $field): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_string($value)) {
            throw new InvalidPageDataException(
                sprintf('Page field [%s] must be a string.', $field)
            );
        }

        return trim($value);
    }

    private function parentId(mixed $value): ?int
    {
        if ($value === null || $value === '' || $value === 0 || $value === '0') {
            return null;
        }

        return $this->positiveInteger($value, 'parent_id');
    }

    private function position(mixed $value): int
    {
        $position = filter_var(
            $value,
            FILTER_VALIDATE_INT,
            FILTER_NULL_ON_FAILURE
        );

        if ($position === null || $position < 0) {
            throw new InvalidPageDataException(
                'Page position must be a non-negative integer.'
            );
        }

        return $position;
    }

    private function positiveInteger(mixed $value, string $field): int
    {
        $integer = filter_var(
            $value,
            FILTER_VALIDATE_INT,
            FILTER_NULL_ON_FAILURE
        );

        if ($integer === null || $integer < 1) {
            throw new InvalidPageDataException(
                sprintf('Page field [%s] must be a positive integer.', $field)
            );
        }

        return $integer;
    }

    /** @return list<int> */
    private function bulkIds(mixed $values): array
    {
        if (!is_array($values)) {
            return [];
        }

        $ids = [];

        foreach ($values as $value) {
            $id = filter_var($value, FILTER_VALIDATE_INT);

            if ($id === false || $id < 1) {
                continue;
            }

            $ids[$id] = $id;
        }

        return array_values($ids);
    }

    private function boolean(mixed $value, string $field): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $boolean = filter_var(
            $value,
            FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE
        );

        if ($boolean === null) {
            throw new InvalidPageDataException(
                sprintf('Page field [%s] must be boolean.', $field)
            );
        }

        return $boolean;
    }
}
