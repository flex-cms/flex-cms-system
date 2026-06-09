<?php

namespace Flex\Core\Filters\Shared;

use Flex\Core\Filters\Interfaces\FilterInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class StatusFilter implements FilterInterface
{
    public function apply(Builder $query, mixed $value): Builder
    {
        switch ($value) {
            case 'active':
                $query->where('is_active', 1);
                $this->applySoftDeleteConstraint($query);
                break;

            case 'inactive':
                $query->where('is_active', 0);
                $this->applySoftDeleteConstraint($query);
                break;

            case 'deleted':
                if ($this->usesSoftDeletes($query)) {
                    $query->onlyTrashed();
                }
                break;
        }

        return $query;
    }

    protected function applySoftDeleteConstraint(Builder $query): void
    {
        if ($this->usesSoftDeletes($query)) {
            $query->whereNull('deleted_at');
        }
    }

    protected function usesSoftDeletes(Builder $query): bool
    {
        return in_array(
            SoftDeletes::class,
            class_uses($query->getModel())
        );
    }
}
