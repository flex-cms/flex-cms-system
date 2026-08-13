<?php

declare(strict_types=1);

namespace Flex\Features\Pages\Repositories;

use Flex\Features\Pages\Models\Page;
use Flex\Features\Pages\Models\PageField;

final class EloquentPageFieldRepository implements PageFieldRepositoryInterface
{
    private const SORTABLE = [
        'id' => 'id',
        'type' => 'type',
        'type_label' => 'type',
        'label' => 'label',
        'key' => 'field_key',
        'group' => 'field_group',
        'order' => 'position',
        'position' => 'position',
    ];

    public function paginate(Page $page, array $query): array
    {
        $number = max(1, (int) ($query['page'] ?? 1));
        $perPage = max(1, min(250, (int) ($query['per_page'] ?? 25)));
        $requestedSort = (string) ($query['sort'] ?? 'position');
        $sortBy = self::SORTABLE[$requestedSort] ?? 'position';
        $direction = ($query['direction'] ?? null) === 'desc' ? 'desc' : 'asc';
        $builder = PageField::query()->where('page_id', $page->id);
        $search = trim((string) ($query['search'] ?? ''));

        if ($search !== '') {
            $builder->where(static function ($query) use ($search): void {
                $query->where('label', 'LIKE', '%' . $search . '%')
                    ->orWhere('field_key', 'LIKE', '%' . $search . '%')
                    ->orWhere('field_group', 'LIKE', '%' . $search . '%');
            });
        }

        $total = (clone $builder)->count();
        $lastPage = max(1, (int) ceil($total / $perPage));
        $number = min($number, $lastPage);
        $rows = $builder->orderBy($sortBy, $direction)
            ->orderBy('id')->forPage($number, $perPage)->get();

        return [
            'data' => $rows->map(static fn (PageField $field): array => [
                'id' => (int) $field->id,
                'type' => $field->type->value,
                'type_label' => $field->type->label(),
                'label' => $field->label,
                'key' => $field->field_key,
                'group' => $field->field_group,
                'order' => (int) $field->position,
                'hint' => $field->hint,
            ])->values()->all(),
            'pagination' => [
                'page' => $number,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => $lastPage,
            ],
        ];
    }

    public function findOrFail(Page $page, int $id): PageField
    {
        return PageField::query()->where('page_id', $page->id)->findOrFail($id);
    }

    public function create(Page $page, array $data): PageField
    {
        return $page->fields()->create($data);
    }

    public function update(PageField $field, array $data): PageField
    {
        $field->fill($data)->save();

        return $field->refresh();
    }

    public function delete(PageField $field): void
    {
        $field->delete();
    }

    public function keyExists(Page $page, string $key, ?int $exceptId = null): bool
    {
        $query = PageField::query()->where('page_id', $page->id)->where('field_key', $key);

        if ($exceptId !== null) {
            $query->whereKeyNot($exceptId);
        }

        return $query->exists();
    }

    public function deleteAll(Page $page): void
    {
        PageField::query()->where('page_id', $page->id)->delete();
    }

    public function transaction(callable $callback): mixed
    {
        return (new PageField())->getConnection()->transaction($callback);
    }
}
