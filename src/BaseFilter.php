<?php

namespace Salehhashemi\Repository;

use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Model;

abstract class BaseFilter
{
    /**
     * @var int
     */
    protected const WILD_BEFORE = 0;

    /**
     * @var int
     */
    protected const WILD_AFTER = 1;

    /**
     * @var int
     */
    protected const WILD_BOTH = 2;

    private Model $model;

    protected QueryBuilder $builder;

    abstract protected function getModelClass(): string;

    abstract public function applyFilter(array $queryParams): QueryBuilder;

    protected function getModel(): Model
    {
        if (! isset($this->model)) {
            $this->model = app($this->getModelClass());
        }

        return $this->model;
    }

    protected function whereLike(string $field, string $value, int $type): self
    {
        if ($value === '') {
            return $this;
        }

        $matchText = match ($type) {
            $this::WILD_BEFORE => '%'.$value,
            $this::WILD_AFTER => $value.'%',
            $this::WILD_BOTH => '%'.$value.'%',
            default => $value,
        };

        $this->getQuery()->where($field, 'like', $matchText);

        return $this;
    }

    protected function whereValue(string $field, string|int|array $value, bool $isNumeric = false): self
    {
        if ($isNumeric && ! is_numeric($value)) {
            return $this;
        }

        if (is_array($value)) {
            if (! empty($value)) {
                $this->getQuery()->whereIn($field, $value);
            }
        } elseif ($value !== '') {
            $this->getQuery()->where($field, $value);
        }

        return $this;
    }

    protected function compare(string $field, string $operator, string $value): self
    {
        if ($value === '') {
            return $this;
        }

        $this->getQuery()->where($field, $operator, $value);

        return $this;
    }

    protected function dateFrom(string $field, string $date): static
    {
        if ($date) {
            $this->getQuery()->where($field, '>=', $date.' 00:00:00');
        }

        return $this;
    }

    protected function dateTo(string $field, string $date): static
    {
        if ($date) {
            $this->getQuery()->where($field, '<=', $date.' 23:59:59');
        }

        return $this;
    }

    public function setQuery(QueryBuilder $builder): void
    {
        $this->builder = $builder;
    }

    protected function getQuery(): QueryBuilder
    {
        if (! isset($this->builder)) {
            $this->builder = $this->getModel()->newQuery();
        }

        return $this->builder;
    }
}
