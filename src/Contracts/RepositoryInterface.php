<?php

namespace Salehhashemi\Repository\Contracts;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface RepositoryInterface
{
    /**
     * It applies the conditions, criteria, relations, and order by to the query,
     * then gets the first result and resets the query
     */
    public function findOne(int|string $primaryKey = null): ?Model;

    /**
     * If the result of the `findOne` function is null, throw a `ModelNotFoundException`
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOneOrFail(int|string $primaryKey = null): Model;

    /**
     * It takes an array of options, applies them to the query, executes the query, and returns the results.
     */
    public function findAll(array $options = []): EloquentCollection;

    /**
     * It returns a collection of key-value pairs from the database.
     *
     * @param  string|null  $key The key to use for the list.
     * @param  string|null  $value The field to use as the value of the select list.
     * @return \Illuminate\Support\Collection A collection of key value pairs.
     */
    public function findList(string $key = null, string $value = null): Collection;

    /**
     * It applies the criteria, relations, and order by to the query, then paginates the results and resets the query
     */
    public function paginate(int $perPage = null): Paginator;

    /**
     * Add a criteria to the query.
     */
    public function addCriteria(CriteriaInterface $criteria): static;

    /**
     * Add an order by clause to the query.
     */
    public function orderBy(string $field, string $direction = 'ASC'): static;

    /**
     * Locks the selected rows so that they can't be updated or deleted by other transactions
     * until the next commit/rollback
     */
    public function lockForUpdate(): static;

    /**
     * Adds a `LOCK IN SHARE MODE` clause to the query
     */
    public function sharedLock(): static;
}
