<?php

namespace Salehhashemi\Repository\Tests\TestSupport\Repositories\Criterias;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Salehhashemi\Repository\Contracts\CriteriaInterface;

class FeaturedPostCriteria implements CriteriaInterface
{
    /**
     * {@inheritDoc}
     */
    public function apply(Model $model, Builder $query): Builder
    {
        $query->where([
            'posts.is_featured' => 1,
        ]);

        return $query;
    }
}
