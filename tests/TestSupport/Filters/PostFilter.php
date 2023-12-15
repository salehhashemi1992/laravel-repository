<?php

namespace Salehhashemi\Repository\Tests\TestSupport\Filters;

use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Salehhashemi\Repository\BaseFilter;
use Salehhashemi\Repository\Tests\TestSupport\Models\Post;

class PostFilter extends BaseFilter
{
    public function applyFilter(array $queryParams): QueryBuilder
    {
        $this
            ->whereLike('title', $queryParams['title'] ?? '', self::WILD_BOTH)
            ->whereValue('status', $queryParams['status'] ?? '')
            ->compare('created_at', '>=', $queryParams['created_from'] ?? '')
            ->compare('created_at', '<=', $queryParams['created_to'] ?? '');

        if (! empty($queryParams['category_id'])) {
            $this->getQuery()->whereHas('categories', function ($query) use ($queryParams) {
                $query->where('categories.id', $queryParams['category_id']);
            });
        }

        return $this->getQuery();
    }

    protected function getModelClass(): string
    {
        return Post::class;
    }
}
