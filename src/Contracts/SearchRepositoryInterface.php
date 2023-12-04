<?php

namespace Salehhashemi\Repository\Contracts;

use Illuminate\Contracts\Pagination\Paginator;

interface SearchRepositoryInterface
{
    /**
     * @param  array  $queryParams Request params
     * @param  array  $options The settings/configuration used for pagination.
     * @return \Illuminate\Contracts\Pagination\Paginator;
     */
    public function search(array $queryParams, array $options = []): Paginator;
}