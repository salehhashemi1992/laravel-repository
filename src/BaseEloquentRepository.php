<?php

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection as GeneralCollection;
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
        $this->perPage = config('perPage');
    }

    /**
     * This function returns the fully qualified class name of the model that the repository is responsible for.
     */
    abstract protected function getModelClass(): string;

    /**
     * {@inheritDoc}
     */
    public function findOne(int|string $primaryKey = null): ?Model
    {
        if (func_num_args() > 0) {
            $this->applyConditions(['id' => $primaryKey]);
        }

        $this->applyCriteria();
        $this->applyRelations();
        $this->applyOrderBy();
        $result = $this->getQuery()->first();
        $this->resetQuery();

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function findOneOrFail(int|string $primaryKey = null): Model
    {
        if (func_num_args() > 0) {
            $this->applyConditions(['id' => $primaryKey]);
        }

        $result = $this->findOne();

        if ($result === null) {
            throw (new ModelNotFoundException())->setModel(
                get_debug_type($this->model),
                $primaryKey
            );
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function findAll(array $options = []): Collection
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
        $this->applyOrderBy();
        $results = $this->getQuery()->get();
        $this->resetQuery();

        return $results;
    }

    /**
     * {@inheritDoc}
     */
    public function findList(string $key = null, string $value = null): GeneralCollection
    {
        $this->applyCriteria();
        $this->applyRelations();
        $this->applyOrderBy();

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
    public function paginate(int $perPage = null): Paginator
    {
        $this->perPage = $perPage ?? $this->perPage;

        $this->applyCriteria();
        $this->applyRelations();
        $this->applyOrderBy();
        $results = $this->getQuery()->paginate($perPage);
        $this->resetQuery();

        return $results;
    }

    /**
     * @param  array  $conditions Conditions
     * @return $this
     */
    protected function applyConditions(array $conditions): self
    {
        $conditions = array_combine($this->aliasFields(array_keys($conditions)), $conditions);
        $this->getQuery()->where($conditions);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addCriteria(CriteriaInterface $criteria): self
    {
        $this->criteria[] = $criteria;

        return $this;
    }

    /**
     * @return $this
     */
    protected function applyCriteria(): self
    {
        foreach ($this->criteria as $criteria) {
            $criteria->apply($this->getModel(), $this->getQuery());
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function orderBy(string $field, string $direction = 'ASC'): self
    {
        $this->orderByFields[$this->aliasField($field)] = $direction;

        return $this;
    }

    protected function applyOrderBy(): void
    {
        $fields = $this->orderByFields;
        if (! $fields) {
            $fields = [$this->aliasField('id') => 'DESC'];
        }

        foreach ($fields as $field => $direction) {
            $this->getQuery()->orderBy($field, $direction);
        }
    }

    /**
     * @param  array  $relations Relations
     * @return $this
     */
    protected function with(array $relations): self
    {
        $this->relations = array_merge($this->relations, $relations);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function lockForUpdate(): self
    {
        $this->getQuery()->lockForUpdate();

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function sharedLock(): self
    {
        $this->getQuery()->sharedLock();

        return $this;
    }

    protected function applyRelations(): void
    {
        if ($this->relations) {
            $this->getQuery()->with($this->relations);
        }
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
            $className = $this->getModelClass();
            $this->model = new $className();
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

    protected function resetQuery(): void
    {
        unset($this->query);
        $this->criteria = [];
        $this->relations = [];
        $this->orderByFields = [];
    }

    /**
     * This function returns the name of the field that will be used to display the record in the list view.
     */
    protected function getDisplayField(): string
    {
        return 'id';
    }
}
