<?php

declare(strict_types=1);

namespace Flex\Features\Shopping\Repositories;

use Flex\Features\Shopping\Models\Category;
use Illuminate\Database\Eloquent\Collection;

interface CategoryRepositoryInterface
{
    public function all(): Collection;

    public function active(): Collection;

    public function find(int $id): ?Category;

    public function findOrFail(int $id): Category;

    public function paginate(
        int $page,
        int $perPage,
        ?string $sortBy = null,
        ?string $sortDirection = null,
        ?string $search = null,
        array $filters = []
    ): array;

    public function create(array $data): Category;

    public function update(Category $category, array $data): Category;

    public function delete(Category $category): void;

    public function restore(Category $category): void;

    public function forceDelete(Category $category): void;

    public function transaction(callable $callback): mixed;

    public function bulkSetActive(array $ids, bool $active): int;

    public function bulkDelete(array $ids): int;
    public function bulkRestore(array $ids): int;

    public function bulkForceDelete(array $ids): int;
}
