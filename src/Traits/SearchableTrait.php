<?php

namespace Salehhashemi\Repository\Traits;

use App\Filters\BaseFilter;
use Illuminate\Contracts\Pagination\Paginator;

trait SearchableTrait
{
    abstract protected function getFilterManager(): BaseFilter;

    /**
     * @param  int  $perPage Rows Per Page
     */
    abstract public function paginate(int $perPage = 10): Paginator;

    public function search(array $queryParams, array $options = []): Paginator
    {
        $this->setQuery($this->getFilterManager()->applyFilter($queryParams));

        return $this->paginate();
    }
}