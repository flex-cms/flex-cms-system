<?php

namespace Flex\Core\Filters\Interfaces;

use Illuminate\Database\Eloquent\Builder;

interface FilterInterface
{
    public function apply(Builder $query, mixed $value): Builder;
}