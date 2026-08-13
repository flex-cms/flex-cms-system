<?php

declare(strict_types=1);

namespace Flex\Features\Pages\Repositories;

use Flex\Features\Pages\Models\Page;
use Flex\Features\Pages\Models\PageOption;
use Illuminate\Database\Eloquent\Collection;

interface PageOptionRepositoryInterface
{
    /** @return Collection<int, PageOption> */
    public function allFor(Page $page): Collection;

    public function find(Page $page, string $key): ?PageOption;

    public function save(
        Page $page,
        string $key,
        string $encodedValue
    ): PageOption;

    /** @param list<string> $keys */
    public function deleteMissing(Page $page, array $keys): int;

    public function delete(Page $page, string $key): int;

    public function transaction(callable $callback): mixed;
}
