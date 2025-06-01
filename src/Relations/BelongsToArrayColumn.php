<?php

declare(strict_types=1);

namespace Mrpunyapal\LaravelExtendedRelationships\Relations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BelongsToArrayColumn extends BelongsTo
{
    /**
     * Create a new belongs to array Column relationship instance.
     */
    public function __construct(Builder $query, Model $child, string $foreignKey, string $ownerKey, ?string $relationName, protected bool $isString = false)
    {
        parent::__construct($query, $child, $foreignKey, $ownerKey, $relationName);
    }

    /**
     * Set the base constraints on the relation query.
     */
    public function addConstraints(): void
    {
        if (! static::$constraints) {
            return;
        }
        $query = $this->getBaseQuery();

        $parentKey = $this->getParentKey();
        if ($parentKey !== null) {
            $searchValue = $this->isString ? (string) $parentKey : $parentKey;
            $this->addJsonContainsConstraint($query, $this->ownerKey, $searchValue);
        }

        $query->whereNotNull($this->ownerKey);
    }

    /**
     * Set the constraints for an eager load of the relation.
     */
    public function addEagerConstraints(array $models): void
    {
        $ids = $this->getEagerModelKeys($models);

        if ($this->isString) {
            // For strings, simple OR with quoted values
            $this->query->where(function ($q) use ($ids) {
                foreach ($ids as $id) {
                    $q->orWhere($this->ownerKey, 'LIKE', '%"' . $id . '"%');
                }
            });
        } else {
            // For integers, use pattern matching with OR logic
            $this->query->where(function ($q) use ($ids) {
                foreach ($ids as $id) {
                    $q->orWhere(function ($subQuery) use ($id) {
                        $patterns = [
                            '%[' . $id . ',%',
                            '%,' . $id . ',%',
                            '%,' . $id . ']%',
                            '%[' . $id . ']%'
                        ];

                        foreach ($patterns as $i => $pattern) {
                            if ($i === 0) {
                                $subQuery->where($this->ownerKey, 'LIKE', $pattern);
                            } else {
                                $subQuery->orWhere($this->ownerKey, 'LIKE', $pattern);
                            }
                        }
                    });
                }
            });
        }
    }

    /**
     * Match the eagerly loaded results to their many parents.
     *
     * @param  string  $relation
     */
    public function match(array $models, Collection $results, $relation): array
    {
        $owner = $this->getOwnerKeyName();
        foreach ($models as $model) {
            $id = $model->getAttribute($this->foreignKey);
            $collection = collect();
            foreach ($results as $data) {
                $ownerValue = $data->{$owner};
                if (is_array($ownerValue) && in_array($id, $ownerValue, true)) {
                    $collection->push($data);
                }
            }
            $model->setRelation($relation, $collection);
        }

        return $models;
    }

    /**
     * Get the results of the relationship.
     */
    public function getResults(): mixed
    {
        return $this->query->get();
    }

    /**
     * Add JSON contains constraint in a database-agnostic way.
     * Uses pattern matching that works across all database engines and Laravel versions.
     */
    protected function addJsonContainsConstraint($query, string $column, $value, string $boolean = 'and'): void
    {
        // Use pattern matching for all databases to ensure compatibility
        // with prefer-lowest dependency resolution in CI environments
        if ($this->isString) {
            // For strings, search for quoted value: "value"
            $method = $boolean === 'or' ? 'orWhere' : 'where';
            $query->$method($column, 'LIKE', '%"' . $value . '"%');
        } else {
            // For integers, use precise pattern matching for unquoted numbers
            $patterns = [
                '%[' . $value . ',%',  // [1,
                '%,' . $value . ',%',  // ,1,
                '%,' . $value . ']%',  // ,1]
                '%[' . $value . ']%'   // [1]
            ];

            if ($boolean === 'or') {
                $query->orWhere(function ($subQuery) use ($column, $patterns) {
                    foreach ($patterns as $i => $pattern) {
                        if ($i === 0) {
                            $subQuery->where($column, 'LIKE', $pattern);
                        } else {
                            $subQuery->orWhere($column, 'LIKE', $pattern);
                        }
                    }
                });
            } else {
                $query->where(function ($subQuery) use ($column, $patterns) {
                    foreach ($patterns as $i => $pattern) {
                        if ($i === 0) {
                            $subQuery->where($column, 'LIKE', $pattern);
                        } else {
                            $subQuery->orWhere($column, 'LIKE', $pattern);
                        }
                    }
                });
            }
        }
    }
}
