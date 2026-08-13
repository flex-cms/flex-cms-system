<?php

declare(strict_types=1);

namespace Flex\Features\Pages\Repositories;

use Flex\Features\Pages\Models\Page;
use Flex\Features\Pages\Models\PageOption;
use Illuminate\Database\Eloquent\Collection;

final class EloquentPageOptionRepository implements
    PageOptionRepositoryInterface
{
    public function allFor(Page $page): Collection
    {
        return PageOption::query()
            ->where('page_id', $page->id)
            ->orderBy('option_key')
            ->get();
    }

    public function find(Page $page, string $key): ?PageOption
    {
        return PageOption::query()
            ->where('page_id', $page->id)
            ->where('option_key', $key)
            ->first();
    }

    public function save(
        Page $page,
        string $key,
        string $encodedValue
    ): PageOption {
        return PageOption::query()->updateOrCreate(
            [
                'page_id' => $page->id,
                'option_key' => $key,
            ],
            [
                'option_value' => $encodedValue,
            ]
        );
    }

    public function deleteMissing(Page $page, array $keys): int
    {
        $query = PageOption::query()
            ->where('page_id', $page->id);

        if ($keys !== []) {
            $query->whereNotIn('option_key', $keys);
        }

        return $query->delete();
    }

    public function delete(Page $page, string $key): int
    {
        return PageOption::query()
            ->where('page_id', $page->id)
            ->where('option_key', $key)
            ->delete();
    }

    public function transaction(callable $callback): mixed
    {
        return (new PageOption())
            ->getConnection()
            ->transaction($callback);
    }
}
