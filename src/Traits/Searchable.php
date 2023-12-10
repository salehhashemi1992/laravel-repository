<?php

namespace Salehhashemi\Repository\Traits;

use Illuminate\Contracts\Pagination\Paginator;
use Salehhashemi\Repository\BaseFilter;

/**
 * @mixin \Salehhashemi\Repository\BaseEloquentRepository
 */
trait Searchable
{
    abstract protected function getFilterManager(): BaseFilter;

    public function search(array $queryParams, ?int $perPage = null): Paginator
    {
        $this->setQuery($this->getFilterManager()->applyFilter($queryParams));

        return $this->paginate($perPage);
    }
}
