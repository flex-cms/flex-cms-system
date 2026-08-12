<?php

declare(strict_types=1);

namespace Flex\Features\Shopping\Repositories;

use Flex\Features\Shopping\Models\Product;

interface ProductRepositoryInterface
{
    public function findOrFail(int $id): Product;

    public function paginate(
        int $page,
        int $perPage,
        ?string $sortBy = null,
        ?string $sortDirection = null,
        ?string $search = null,
        array $filters = []
    ): array;

    public function create(array $data): Product;

    public function update(Product $product, array $data): Product;

    public function delete(Product $product): void;

    public function restore(Product $product): void;

    public function forceDelete(Product $product): void;

    public function transaction(callable $callback): mixed;

    public function bulkSetStatus(array $ids, string $status): int;

    public function bulkDelete(array $ids): int;

    public function bulkRestore(array $ids): int;

    public function bulkForceDelete(array $ids): int;
}
