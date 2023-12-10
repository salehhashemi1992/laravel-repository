<?php

namespace Salehhashemi\Repository\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

interface CriteriaInterface
{
    /**
     * Apply the criteria to the given Eloquent query builder.
     */
    public function apply(Model $model, Builder $query): Builder;
}
