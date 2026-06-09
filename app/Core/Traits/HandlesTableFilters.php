<?php

namespace Flex\Core\Traits;

use Illuminate\Database\Eloquent\SoftDeletes;

trait HandlesTableFilters
{
    protected function applySorting($query, array $allowedSorts, string $defaultSort = 'id', string $defaultDirection = 'asc')
    {
        $sort = $_GET['sort'] ?? $defaultSort;
        $direction = strtolower($_GET['direction'] ?? $defaultDirection);

        if (!in_array($sort, $allowedSorts, true)) {
            $sort = $defaultSort;
        }

        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = $defaultDirection;
        }

        return $query->orderBy($sort, $direction);
    }

    protected function applySearch($query, array $columns, ?string $search = null)
    {
        $search ??= trim($_GET['search'] ?? '');

        if ($search === '') {
            return $query;
        }

        return $query->where(function ($q) use ($columns, $search) {
            foreach ($columns as $index => $column) {
                if ($index === 0) {
                    $q->where($column, 'LIKE', "%{$search}%");
                    continue;
                }

                $q->orWhere($column, 'LIKE', "%{$search}%");
            }
        });
    }

    protected function applyFilters(
        $query,
        array $searchColumns = [],
        array $sortableColumns = [],
        array $filters = [],
        string $defaultSort = 'id',
        string $defaultDirection = 'asc'
    ) {
        $this->applySearch($query, $searchColumns);

        $this->applySelectFilters($query, $filters);

        $this->applySorting(
            $query,
            $sortableColumns,
            $defaultSort,
            $defaultDirection
        );

        return $query;
    }

    protected function applyStatusFilter($query, string $value)
    {
        switch ($value) {
            case 'active':
                $query->where('is_active', 1);

                if ($this->usesSoftDeletes($query)) {
                    $query->whereNull('deleted_at');
                }
                break;

            case 'inactive':
                $query->where('is_active', 0);

                if ($this->usesSoftDeletes($query)) {
                    $query->whereNull('deleted_at');
                }
                break;

            case 'deleted':
                if ($this->usesSoftDeletes($query)) {
                    $query->onlyTrashed();
                }
                break;
        }
    }

    protected function applySelectFilters($query, array $filters = [])
    {
        foreach ($filters as $key => $filterClass) {
            $value = $_GET[$key] ?? null;

            if ($value === null || $value === '') {
                continue;
            }

            if (!class_exists($filterClass)) {
                continue;
            }

            $filter = new $filterClass();

            if (!method_exists($filter, 'apply')) {
                continue;
            }

            $query = $filter->apply($query, $value);
        }

        return $query;
    }

    protected function usesSoftDeletes($query): bool
    {
        $model = $query->getModel();
        return in_array(SoftDeletes::class, class_uses($model));
    }
}