<?php

declare(strict_types=1);

namespace Flex\Features\Pages\Repositories;

use Flex\Features\Pages\Models\Page;
use Flex\Features\Pages\Models\PageElement;
use Illuminate\Database\Eloquent\Collection;

interface PageElementRepositoryInterface
{
    /** @return Collection<int, PageElement> */
    public function allFor(Page $page): Collection;

    public function findForPage(Page $page, int $id): ?PageElement;

    /** @param array<string, mixed> $data */
    public function create(Page $page, array $data): PageElement;

    /** @param array<string, mixed> $data */
    public function update(PageElement $element, array $data): PageElement;

    /** @param list<int> $ids */
    public function deleteMissing(Page $page, array $ids): int;

    public function delete(PageElement $element): void;

    public function transaction(callable $callback): mixed;
}
