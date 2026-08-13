<?php

declare(strict_types=1);

namespace Flex\Features\Pages\Repositories;

use Flex\Features\Pages\Models\Page;
use Flex\Features\Pages\Models\PageField;

interface PageFieldRepositoryInterface
{
    /** @return array<string, mixed> */
    public function paginate(Page $page, array $query): array;

    public function findOrFail(Page $page, int $id): PageField;

    /** @param array<string, mixed> $data */
    public function create(Page $page, array $data): PageField;

    /** @param array<string, mixed> $data */
    public function update(PageField $field, array $data): PageField;

    public function delete(PageField $field): void;

    public function keyExists(Page $page, string $key, ?int $exceptId = null): bool;

    public function deleteAll(Page $page): void;

    public function transaction(callable $callback): mixed;
}
