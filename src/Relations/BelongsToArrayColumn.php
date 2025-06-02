<?php

declare(strict_types=1);

namespace MrPunyapal\LaravelExtendedRelationships\Relations;

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

        $query->when($this->isString, function ($q) {
            $q->whereJsonContains($this->ownerKey, (string) $this->getParentKey());
        }, function ($q) {
            $q->whereJsonContains($this->ownerKey, $this->getParentKey());
        });

        $query->whereNotNull($this->ownerKey);
    }

    /**
     * Set the constraints for an eager load of the relation.
     */
    public function addEagerConstraints(array $models): void
    {
        $ids = $this->getEagerModelKeys($models);
        $this->query->where(function ($q) use ($ids) {
            foreach ($ids as $id) {
                $q->when($this->isString, function ($q) use ($id) {
                    $q->orWhereJsonContains($this->ownerKey, (string) $id);
                }, function ($q) use ($id) {
                    $q->orWhereJsonContains($this->ownerKey, $id);
                });
            }
        });
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
}
