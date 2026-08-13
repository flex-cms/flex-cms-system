<?php

declare(strict_types=1);

namespace Flex\Features\Pages\Repositories;

use Flex\Features\Pages\Models\Page;
use Flex\Features\Pages\Models\PageElement;
use Illuminate\Database\Eloquent\Collection;

final class EloquentPageElementRepository implements
    PageElementRepositoryInterface
{
    public function allFor(Page $page): Collection
    {
        return PageElement::query()
            ->where('page_id', $page->id)
            ->orderBy('position')
            ->orderBy('id')
            ->get();
    }

    public function findForPage(Page $page, int $id): ?PageElement
    {
        return PageElement::query()
            ->where('page_id', $page->id)
            ->find($id);
    }

    public function create(Page $page, array $data): PageElement
    {
        return PageElement::query()->create([
            ...$data,
            'page_id' => $page->id,
        ]);
    }

    public function update(
        PageElement $element,
        array $data
    ): PageElement {
        $element->fill($data);
        $element->save();

        return $element->refresh();
    }

    public function deleteMissing(Page $page, array $ids): int
    {
        $query = PageElement::query()
            ->where('page_id', $page->id);

        if ($ids !== []) {
            $query->whereNotIn('id', $ids);
        }

        return $query->delete();
    }

    public function delete(PageElement $element): void
    {
        $element->delete();
    }

    public function transaction(callable $callback): mixed
    {
        return (new PageElement())
            ->getConnection()
            ->transaction($callback);
    }
}
