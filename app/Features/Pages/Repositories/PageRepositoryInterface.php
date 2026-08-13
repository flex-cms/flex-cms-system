<?php

declare(strict_types=1);

namespace Flex\Features\Pages\Repositories;

use Flex\Features\Pages\Models\Page;
use Illuminate\Database\Eloquent\Collection;

interface PageRepositoryInterface
{
    /**
     * @param array<string, mixed> $filters
     * @return array{data: list<array<string, mixed>>, pagination: array{page: int, per_page: int, total: int, last_page: int}}
     */
    public function paginate(
        int $page,
        int $perPage,
        ?string $sortBy = null,
        ?string $sortDirection = null,
        ?string $search = null,
        array $filters = []
    ): array;

    /** @return Collection<int, Page> */
    public function all(
        ?string $search = null,
        ?string $status = null
    ): Collection;

    public function find(int $id): ?Page;

    public function findOrFail(int $id): Page;

    public function findByFullSlug(string $fullSlug): ?Page;

    public function slugExists(
        string $slug,
        ?int $parentId = null,
        ?int $exceptId = null
    ): bool;

    /** @param array<string, mixed> $data */
    public function create(array $data): Page;

    /** @param array<string, mixed> $data */
    public function update(Page $page, array $data): Page;

    public function delete(Page $page): void;

    public function restore(Page $page): void;

    public function forceDelete(Page $page): void;

    public function setActive(Page $page, bool $active): Page;

    /**
     * @param list<array{id: int, position: int}> $positions
     */
    public function updatePositions(array $positions): void;

    public function transaction(callable $callback): mixed;
}
