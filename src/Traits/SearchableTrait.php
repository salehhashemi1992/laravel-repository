<?php

namespace Salehhashemi\Repository\Traits;

use Illuminate\Contracts\Pagination\Paginator;
use Salehhashemi\Repository\BaseFilter;

/**
 * @mixin \Salehhashemi\Repository\BaseEloquentRepository
 */
trait SearchableTrait
{
    abstract protected function getFilterManager(): BaseFilter;

    /**
     * {@inheritDoc}
     */
    public function search(array $queryParams, int $perPage = null): Paginator
    {
        $this->setQuery($this->getFilterManager()->applyFilter($queryParams));

        return $this->paginate($perPage);
    }
}
