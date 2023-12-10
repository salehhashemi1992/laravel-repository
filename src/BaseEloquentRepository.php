<?php

namespace Salehhashemi\Repository;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Salehhashemi\Repository\Contracts\CriteriaInterface;
use Salehhashemi\Repository\Contracts\RepositoryInterface;

abstract class BaseEloquentRepository implements RepositoryInterface
{
    /**
     * @var CriteriaInterface[]
     */
    protected array $criteria = [];

    private array $relations = [];

    protected array $orderByFields = [];

    private Builder $query;

    private Model $model;

    private int $perPage;

    public function __construct()
    {
        $this->perPage = config('repository.perPage');
    }

    /**
     * This function returns the fully qualified class name of the model that the repository is responsible for.
     */
    abstract protected function getModelClass(): string;

    /**
     * This function returns the name of the field that will be used to display the record in the list view.
     */
    protected function getDisplayField(): string
    {
        return 'id';
    }

    /**
     * {@inheritDoc}
     */
    public function findOne(int|string|null $primaryKey = null): ?Model
    {
        if (func_num_args() > 0) {
            $this->applyConditions(['id' => $primaryKey]);
        }

        $this->applyCriteria();
        $this->applyRelations();
        $this->applyOrder();
        $result = $this->getQuery()->first();
        $this->resetQuery();

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function findOneOrFail(int|string|null $primaryKey = null): Model
    {
        if (func_num_args() > 0) {
            $this->applyConditions(['id' => $primaryKey]);
        }

        $result = $this->findOne();

        if ($result === null) {
            throw (new ModelNotFoundException())->setModel(
                $this->model::class,
                $primaryKey ?? ''
            );
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function findAll(array $options = []): EloquentCollection
    {
        $options += [
            'limit' => null,
            'offset' => null,
        ];

        if (is_numeric($options['limit']) && $options['limit'] > 0) {
            $this->getQuery()->limit($options['limit']);
        }

        if (is_numeric($options['offset']) && $options['offset'] > 0) {
            $this->getQuery()->skip($options['offset']);
        }

        $this->applyCriteria();
        $this->applyRelations();
        $this->applyOrder();
        $results = $this->getQuery()->get();
        $this->resetQuery();

        return $results;
    }

    /**
     * {@inheritDoc}
     */
    public function findList(?string $key = null, ?string $value = null): Collection
    {
        $this->applyCriteria();
        $this->applyRelations();
        $this->applyOrder();

        $key = $key ?? $this->getModel()->getKeyName();
        $value = $value ?? $this->getDisplayField();

        $results = $this->getQuery()
            ->select([$key, $value])
            ->get()
            ->pluck($value, $key);

        $this->resetQuery();

        return $results;
    }

    /**
     * {@inheritDoc}
     */
    public function paginate(?int $perPage = null): Paginator
    {
        $perPage = $this->preparePageSize($perPage);

        $this->applyCriteria();
        $this->applyRelations();
        $this->applyOrder();
        $results = $this->getQuery()->paginate($perPage);
        $this->resetQuery();

        return $results;
    }

    /**
     * Prepare the page size for pagination.
     *
     * @throws \InvalidArgumentException
     */
    protected function preparePageSize(?int $perPage = null): int
    {
        if ($perPage <= 0) {
            throw new InvalidArgumentException('Invalid page size');
        }

        return min($perPage, $this->perPage);
    }

    /**
     * Apply conditions to the query based on an array of conditions.
     */
    protected function applyConditions(array $conditions): static
    {
        $conditions = array_combine($this->aliasFields(array_keys($conditions)), $conditions);
        $this->getQuery()->where($conditions);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addCriteria(CriteriaInterface $criteria): static
    {
        $this->criteria[] = $criteria;

        return $this;
    }

    /**
     * Apply criteria to the query based on registered criteria objects.
     */
    protected function applyCriteria(): static
    {
        foreach ($this->criteria as $criteria) {
            $criteria->apply($this->getModel(), $this->getQuery());
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function orderBy(string $field, string $direction = 'ASC'): static
    {
        $this->orderByFields[$this->aliasField($field)] = $direction;

        return $this;
    }

    /**
     * Apply ordering to the query based on specified fields and directions.
     */
    protected function applyOrder(): static
    {
        $fields = $this->orderByFields;
        if (! $fields) {
            $fields = [$this->aliasField('id') => 'DESC'];
        }

        foreach ($fields as $field => $direction) {
            $this->getQuery()->orderBy($field, $direction);
        }

        return $this;
    }

    /**
     * Add relations to be eager-loaded when querying.
     */
    protected function with(array $relations): static
    {
        $this->relations = array_merge($this->relations, $relations);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function lockForUpdate(): static
    {
        $this->getQuery()->lockForUpdate();

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function sharedLock(): static
    {
        $this->getQuery()->sharedLock();

        return $this;
    }

    /**
     * Apply eager-loading relations to the query.
     */
    protected function applyRelations(): static
    {
        if ($this->relations) {
            $this->getQuery()->with($this->relations);
        }

        return $this;
    }

    /**
     * It takes an array of fields, and returns an array of fields, but with each field aliased
     */
    protected function aliasFields(array $fields): array
    {
        return array_map(function ($field) {
            if (is_numeric($field)) {
                return $field;
            }

            return $this->aliasField($field);
        }, $fields);
    }

    /**
     * Alias a field with the table's current alias.
     */
    protected function aliasField(string $field): string
    {
        if (str_contains($field, '.')) {
            return $field;
        }

        return $this->getTableName().'.'.$field;
    }

    /**
     * Get the model instance associated with the repository.
     */
    protected function getModel(): Model
    {
        if (! isset($this->model)) {
            $this->model = app($this->getModelClass());
        }

        return $this->model;
    }

    /**
     * Return Model's Table Name
     */
    protected function getTableName(): string
    {
        return $this->getModel()->getTable();
    }

    /**
     * Set the query builder instance for the repository.
     */
    protected function setQuery(Builder $query): void
    {
        $this->query = $query;
    }

    /**
     * Get the query builder instance for the repository.
     */
    protected function getQuery(): Builder
    {
        if (! isset($this->query)) {
            $this->query = $this->getModel()->newQuery();
        }

        return $this->query;
    }

    /**
     * Reset the query builder and related properties.
     */
    protected function resetQuery(): void
    {
        unset($this->query);
        $this->criteria = [];
        $this->relations = [];
        $this->orderByFields = [];
    }
}
