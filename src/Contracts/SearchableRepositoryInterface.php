<?php

namespace Salehhashemi\Repository\Contracts;

use Illuminate\Contracts\Pagination\Paginator;

interface SearchableRepositoryInterface
{
    /**
     * Search for records based on the provided query parameters and paginate the results.
     */
    public function search(array $queryParams, ?int $perPage = null): Paginator;
}
