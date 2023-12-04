<?php

namespace Salehhashemi\Repository\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

interface CriteriaInterface
{
    /**
     * @param  \Illuminate\Database\Eloquent\Model  $model Model
     * @param  \Illuminate\Database\Eloquent\Builder  $query Query
     */
    public function apply(Model $model, Builder $query): Builder;
}