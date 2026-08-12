<?php

declare(strict_types=1);

namespace Flex\Features\Shopping\Services;

use Flex\Core\Support\Slug;
use Flex\Features\Shopping\Models\Category;
use Flex\Features\Shopping\Repositories\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;

final class CategoryService
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categories
    ) {
    }

    /**
     * @return Collection<int, Category>
     */
    public function all(): Collection
    {
        return $this->categories->all();
    }

    public function paginate(array $input): array
    {
        $page = max(
            1,
            (int) ($input['page'] ?? 1)
        );

        $perPage = (int) ($input['per_page'] ?? 25);

        if (!in_array($perPage, [3, 5, 10, 20, 25, 50, 100], true)) {
            $perPage = 25;
        }

        $sortBy = isset($input['sort'])
            ? trim((string) $input['sort'])
            : null;

        $sortDirection = isset($input['direction'])
            ? strtolower(trim((string) $input['direction']))
            : null;

        if (
            $sortDirection !== 'asc'
            && $sortDirection !== 'desc'
        ) {
            $sortDirection = null;
        }

        $search = isset($input['search'])
            ? trim((string) $input['search'])
            : null;

        $filters = isset($input['filter'])
            && is_array($input['filter'])
            ? $input['filter']
            : [];

        return $this->categories->paginate(
            page: $page,
            perPage: $perPage,
            sortBy: $sortBy,
            sortDirection: $sortDirection,
            search: $search,
            filters: $filters
        );
    }

    /**
     * @return Collection<int, Category>
     */
    public function parentOptions(?int $excludeId = null): Collection
    {
        return $this->categories
            ->active()
            ->filter(
                static fn(Category $category): bool =>
                $excludeId === null
                || $category->id !== $excludeId
            )
            ->values();
    }

    public function findOrFail(int $id): Category
    {
        return $this->categories->findOrFail($id);
    }

    public function create(array $input): Category
    {
        $data = $this->normalize($input);

        return $this->categories->create($data);
    }

    public function update(
        int $id,
        array $input
    ): Category {
        $category = $this->findOrFail($id);

        $data = $this->normalize(
            $input,
            $category
        );

        if (
            isset($data['parent_id'])
            && $data['parent_id'] === $category->id
        ) {
            throw new InvalidArgumentException(
                'Категорията не може да бъде собствена родителска категория.'
            );
        }

        return $this->categories->update(
            $category,
            $data
        );
    }

    public function delete(int $id): void
    {
        $category = $this->findOrFail($id);

        $this->categories->delete(
            $category
        );
    }

    public function restore(int $id): void
    {
        $category = $this->findOrFail($id);

        if (!$category->trashed()) {
            return;
        }

        $this->categories->restore(
            $category
        );
    }

    public function forceDelete(int $id): void
    {
        $category = $this->findOrFail($id);

        $this->categories->forceDelete(
            $category
        );
    }

    public function toggle(int $id): Category
    {
        $category = $this->findOrFail($id);

        return $this->categories->update(
            $category,
            [
                'is_active' => !$category->is_active,
            ]
        );
    }

    public function bulk(array $input): array
    {
        $action = trim(
            (string) ($input['action'] ?? '')
        );

        $ids = array_values(
            array_unique(
                array_filter(
                    array_map(
                        static fn(mixed $id): int => (int) $id,
                        is_array($input['ids'] ?? null)
                        ? $input['ids']
                        : []
                    ),
                    static fn(int $id): bool => $id > 0
                )
            )
        );

        if ($ids === []) {
            throw new InvalidArgumentException(
                'Не са избрани валидни категории.'
            );
        }

        $affected = match ($action) {
            'activate' => $this->categories->bulkSetActive(
                $ids,
                true
            ),

            'deactivate' => $this->categories->bulkSetActive(
                $ids,
                false
            ),

            'trash' => $this->categories->bulkDelete(
                $ids
            ),

            'restore' => $this->categories->bulkRestore(
                $ids
            ),

            'force-delete' => $this->categories->bulkForceDelete(
                $ids
            ),

            default => throw new InvalidArgumentException(
                'Невалидно групово действие.'
            ),
        };

        $message = match ($action) {
            'activate' => sprintf(
                'Активирани категории: %d.',
                $affected
            ),

            'deactivate' => sprintf(
                'Деактивирани категории: %d.',
                $affected
            ),

            'trash' => sprintf(
                'Преместени в кошчето категории: %d.',
                $affected
            ),

            'restore' => sprintf(
                'Възстановени категории: %d.',
                $affected
            ),

            'force-delete' => sprintf(
                'Изтрити завинаги категории: %d.',
                $affected
            ),
        };

        return [
            'affected' => $affected,
            'message' => $message,
        ];
    }

    private function normalize(
        array $input,
        ?Category $category = null
    ): array {
        $name = trim(
            (string) ($input['name'] ?? '')
        );

        if ($name === '') {
            throw new InvalidArgumentException(
                'Името на категорията е задължително.'
            );
        }

        $slug = trim(
            (string) ($input['slug'] ?? '')
        );

        if ($slug === '') {
            $slug = Slug::make(
                $name
            );
        }

        $slug = $this->ensureUniqueSlug(
            $slug,
            $category?->id
        );

        $parentId = $input['parent_id'] ?? null;

        if (
            $parentId === ''
            || $parentId === null
            || $parentId === '0'
            || $parentId === 0
        ) {
            $parentId = null;
        } else {
            $parentId = (int) $parentId;
        }

        return [
            'parent_id' => $parentId,

            'name' => $name,

            'slug' => $slug,

            'description' => $this->nullableString(
                $input['description'] ?? null
            ),

            'image' => $this->nullableString(
                $input['image'] ?? null
            ),

            'meta_title' => $this->nullableString(
                $input['meta_title'] ?? null
            ),

            'meta_description' => $this->nullableString(
                $input['meta_description'] ?? null
            ),

            'sort_order' => max(
                0,
                (int) ($input['sort_order'] ?? 0)
            ),

            'is_active' => $this->toBoolean(
                $input['is_active'] ?? false
            ),
        ];
    }

    private function ensureUniqueSlug(
        string $slug,
        ?int $ignoreId = null
    ): string {
        $base = Slug::make(
            $slug
        );

        $candidate = $base;
        $counter = 2;

        while (true) {
            $query = Category::query()
                ->withTrashed()
                ->where(
                    'slug',
                    $candidate
                );

            if ($ignoreId !== null) {
                $query->where(
                    'id',
                    '!=',
                    $ignoreId
                );
            }

            if (!$query->exists()) {
                return $candidate;
            }

            $candidate = $base
                . '-'
                . $counter;

            $counter++;
        }
    }

    private function nullableString(
        mixed $value
    ): ?string {
        $value = trim(
            (string) ($value ?? '')
        );

        return $value === ''
            ? null
            : $value;
    }

    private function toBoolean(
        mixed $value
    ): bool {
        if (is_bool($value)) {
            return $value;
        }

        return in_array(
            strtolower(
                (string) $value
            ),
            [
                '1',
                'true',
                'yes',
                'on',
            ],
            true
        );
    }
}
