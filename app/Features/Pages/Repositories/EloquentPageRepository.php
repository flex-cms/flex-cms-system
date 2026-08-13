<?php

declare(strict_types=1);

namespace Flex\Features\Pages\Repositories;

use Flex\Features\Pages\Models\Page;
use Illuminate\Database\Eloquent\Collection;

final class EloquentPageRepository implements PageRepositoryInterface
{
    private const SORTABLE_COLUMNS = [
        'id',
        'name',
        'slug',
        'full_slug',
        'position',
        'is_active',
        'created_at',
        'updated_at',
    ];

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
        $sortBy = in_array($sortBy, self::SORTABLE_COLUMNS, true)
            ? $sortBy
            : 'position';
        $sortDirection = $sortDirection === 'desc' ? 'desc' : 'asc';
        $query = Page::query();
        $search = trim((string) $search);

        if ($search !== '') {
            $query->where(static function ($query) use ($search): void {
                $query
                    ->where('name', 'LIKE', '%' . $search . '%')
                    ->orWhere('slug', 'LIKE', '%' . $search . '%')
                    ->orWhere('full_slug', 'LIKE', '%' . $search . '%');
            });
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
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min($page, $lastPage);
        $rows = $query
            ->orderBy($sortBy, $sortDirection)
            ->orderBy('id')
            ->forPage($page, $perPage)
            ->get();

        return [
            'data' => $rows->map(static function (Page $page): array {
                $depth = max(0, substr_count((string) $page->full_slug, '/'));

                return [
                    'id' => (int) $page->id,
                    'name' => $page->name,
                    'display_name' => str_repeat('— ', $depth) . $page->name,
                    'full_slug' => $page->full_slug,
                    'position' => (int) $page->position,
                    'is_active' => (bool) $page->is_active,
                    'status' => $page->trashed()
                        ? 'deleted'
                        : ($page->is_active ? 'active' : 'inactive'),
                    'created_at' => $page->created_at?->toAtomString(),
                    'updated_at' => $page->updated_at?->toAtomString(),
                    'deleted_at' => $page->deleted_at?->toAtomString(),
                ];
            })->values()->all(),
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => $lastPage,
            ],
        ];
    }

    public function all(
        ?string $search = null,
        ?string $status = null
    ): Collection {
        $query = Page::query();

        $search = trim((string) $search);

        if ($search !== '') {
            $query->where(
                static function ($query) use ($search): void {
                    $query
                        ->where('name', 'LIKE', '%' . $search . '%')
                        ->orWhere('slug', 'LIKE', '%' . $search . '%')
                        ->orWhere('full_slug', 'LIKE', '%' . $search . '%');
                }
            );
        }

        if ($status === 'deleted') {
            $query->onlyTrashed();
        } elseif ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        return $query
            ->orderBy('position')
            ->orderBy('id')
            ->get();
    }

    public function find(int $id): ?Page
    {
        return Page::query()
            ->withTrashed()
            ->with([
                'pageOptions',
                'elements.children',
            ])
            ->find($id);
    }

    public function findOrFail(int $id): Page
    {
        return Page::query()
            ->withTrashed()
            ->with([
                'pageOptions',
                'elements.children',
            ])
            ->findOrFail($id);
    }

    public function findByFullSlug(string $fullSlug): ?Page
    {
        return Page::query()
            ->where('full_slug', $fullSlug)
            ->first();
    }

    public function slugExists(
        string $slug,
        ?int $parentId = null,
        ?int $exceptId = null
    ): bool {
        $query = Page::query()
            ->withTrashed()
            ->where('slug', $slug);

        $parentId === null
            ? $query->whereNull('parent_id')
            : $query->where('parent_id', $parentId);

        if ($exceptId !== null) {
            $query->whereKeyNot($exceptId);
        }

        return $query->exists();
    }

    public function create(array $data): Page
    {
        return Page::query()->create($data);
    }

    public function update(Page $page, array $data): Page
    {
        $page->fill($data);
        $page->save();

        return $page->refresh();
    }

    public function delete(Page $page): void
    {
        $page->delete();
    }

    public function restore(Page $page): void
    {
        $page->restore();
    }

    public function forceDelete(Page $page): void
    {
        $page->forceDelete();
    }

    public function setActive(Page $page, bool $active): Page
    {
        $page->is_active = $active;
        $page->save();

        return $page->refresh();
    }

    public function updatePositions(array $positions): void
    {
        foreach ($positions as $item) {
            Page::query()
                ->whereKey($item['id'])
                ->update([
                    'position' => $item['position'],
                ]);
        }
    }

    public function transaction(callable $callback): mixed
    {
        return (new Page())
            ->getConnection()
            ->transaction($callback);
    }
}
