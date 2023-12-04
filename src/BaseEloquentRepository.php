<?php

use Illuminate\Contracts\Pagination\Paginator;
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

    protected array $relations = [];

    protected array $orderByFields = [];

    protected string $modelClass;

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
    public function paginate(int $perPage = 10): Paginator
    {
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

    protected function resetQuery(): void
    {
        unset($this->query);
        $this->criteria = [];
        $this->relations = [];
        $this->orderByFields = [];
    }

    /**
     * {@inheritDoc}
     */
    protected function getDisplayField(): string
    {
        return 'id';
    }
}
