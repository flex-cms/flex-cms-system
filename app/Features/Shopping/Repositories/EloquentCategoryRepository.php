<?php

declare(strict_types=1);

namespace Flex\Features\Shopping\Repositories;

use Flex\Features\Shopping\Models\Category;
use Illuminate\Database\Eloquent\Collection;

final class EloquentCategoryRepository implements CategoryRepositoryInterface
{
    private const SORTABLE_COLUMNS = [
        'id',
        'name',
        'slug',
        'sort_order',
        'is_active',
        'created_at',
        'updated_at',
    ];

    public function all(): Collection
    {
        return Category::query()
            ->with('parent')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function active(): Collection
    {
        return Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function find(int $id): ?Category
    {
        return Category::query()
            ->withTrashed()
            ->find($id);
    }

    public function findOrFail(int $id): Category
    {
        return Category::query()
            ->withTrashed()
            ->findOrFail($id);
    }

    public function paginate(
        int $page,
        int $perPage,
        ?string $sortBy = null,
        ?string $sortDirection = null,
        ?string $search = null,
        array $filters = []
    ): array {
        $page = max(1, $page);
        $perPage = max(1, min(250, $perPage));

        $sortBy = in_array(
            $sortBy,
            self::SORTABLE_COLUMNS,
            true
        )
            ? $sortBy
            : 'sort_order';

        $sortDirection = $sortDirection === 'desc'
            ? 'desc'
            : 'asc';

        $query = Category::query()
            ->with([
                'parent:id,name',
            ]);

        $search = trim((string) $search);

        if ($search !== '') {
            $query->where(
                static function ($query) use ($search): void {
                    $query
                        ->where('name', 'LIKE', '%' . $search . '%')
                        ->orWhere('slug', 'LIKE', '%' . $search . '%');
                }
            );
        }

        $status = $filters['status'] ?? null;

        if ($status === 'deleted') {
            $query->onlyTrashed();
        } elseif ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        $total = (clone $query)->count();

        $lastPage = max(
            1,
            (int) ceil($total / $perPage)
        );

        $page = min(
            $page,
            $lastPage
        );

        $rows = $query
            ->orderBy($sortBy, $sortDirection)
            ->orderBy('id')
            ->forPage($page, $perPage)
            ->get();

        $data = $rows
            ->map(
                static fn(Category $category): array => [
                    'id' => (int) $category->id,
                    'parent_id' => $category->parent_id !== null
                        ? (int) $category->parent_id
                        : null,
                    'parent_name' => $category->parent?->name,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'sort_order' => (int) $category->sort_order,
                    'is_active' => (bool) $category->is_active,
                    'created_at' => $category->created_at?->toAtomString(),
                    'updated_at' => $category->updated_at?->toAtomString(),
                    'deleted_at' => $category->deleted_at?->toAtomString(),
                ]
            )
            ->values()
            ->all();

        return [
            'data' => $data,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => $lastPage,
            ],
        ];
    }

    public function create(array $data): Category
    {
        return Category::query()->create($data);
    }

    public function update(
        Category $category,
        array $data
    ): Category {
        $category->fill($data);
        $category->save();

        return $category->refresh();
    }

    public function delete(Category $category): void
    {
        $category->delete();
    }

    public function restore(Category $category): void
    {
        $category->restore();
    }

    public function forceDelete(Category $category): void
    {
        $category->forceDelete();
    }

    public function transaction(callable $callback): mixed
    {
        return (new Category())
            ->getConnection()
            ->transaction($callback);
    }

    public function bulkSetActive(
        array $ids,
        bool $active
    ): int {
        return Category::query()
            ->whereIn('id', $ids)
            ->update([
                'is_active' => $active,
            ]);
    }

    public function bulkDelete(
        array $ids
    ): int {
        $categories = Category::query()
            ->whereIn('id', $ids)
            ->get();

        $affected = 0;

        foreach ($categories as $category) {
            if ($category->delete()) {
                $affected++;
            }
        }

        return $affected;
    }

    public function bulkRestore(
        array $ids
    ): int {
        $categories = Category::query()
            ->onlyTrashed()
            ->whereIn('id', $ids)
            ->get();

        $affected = 0;

        foreach ($categories as $category) {
            if ($category->restore()) {
                $affected++;
            }
        }

        return $affected;
    }

    public function bulkForceDelete(
        array $ids
    ): int {
        $categories = Category::query()
            ->onlyTrashed()
            ->whereIn('id', $ids)
            ->get();

        $affected = 0;

        foreach ($categories as $category) {
            if ($category->forceDelete()) {
                $affected++;
            }
        }

        return $affected;
    }
}
