<?php

namespace Salehhashemi\Repository\Contracts;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface RepositoryInterface
{
    /**
     * > It applies the conditions, criteria, relations, and order by to the query,
     * then gets the first result and resets the query
     *
     * @param  int|string|null  $primaryKey The primary key of the model you want to find.
     * @return null|\Illuminate\Database\Eloquent\Model The first result of the query.
     */
    public function findOne(int|string $primaryKey = null): ?Model;

    /**
     * > If the result of the `findOne` function is null, throw a `ModelNotFoundException`
     *
     * @param  int|string|null  $primaryKey Primary key
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException<\Illuminate\Database\Eloquent\Model>
     */
    public function findOneOrFail(int|string $primaryKey = null): Model;

    /**
     * It takes an array of options, applies them to the query, executes the query, and returns the results
     *
     * @param  array  $options An array of options to pass to the finder.
     * @return \Illuminate\Support\Collection A collection of results.
     */
    public function findAll(array $options = []): Collection;

    /**
     * > It returns a collection of key-value pairs from the database
     *
     * @param  string|null  $key The key to use for the list.
     * @param  string|null  $value The field to use as the value of the select list.
     * @return \Illuminate\Support\Collection A collection of key value pairs.
     */
    public function findList(string $key = null, string $value = null): Collection;

    /**
     * It applies the criteria, relations, and order by to the query, then paginates the results and resets the query
     *
     * @param  int  $perPage The number of items to show per page.
     * @return \Illuminate\Contracts\Pagination\Paginator A paginated collection of results.
     */
    public function paginate(int $perPage): Paginator;

    /**
     * @param  CriteriaInterface  $criteria Criteria
     * @return $this
     */
    public function addCriteria(CriteriaInterface $criteria): self;

    /**
     * @param  string  $field Field
     * @param  string  $direction Direction
     * @return $this
     */
    public function orderBy(string $field, string $direction = 'ASC'): self;

    /**
     * > Locks the selected rows so that they can't be updated or deleted by other transactions until the next
     * commit/rollback
     *
     * @return $this The query builder instance.
     */
    public function lockForUpdate(): self;

    /**
     * > Adds a `LOCK IN SHARE MODE` clause to the query
     *
     * @return $this The query builder instance.
     */
    public function sharedLock(): self;
}
