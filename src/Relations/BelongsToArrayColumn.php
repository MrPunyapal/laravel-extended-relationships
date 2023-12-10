<?php

namespace Mrpunyapal\LaravelExtendedRelationships\Relations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BelongsToArrayColumn extends BelongsTo
{
    /**
     * Indicates whether the value is a string.
     *
     * @var bool
     */
    protected $isString;

    /**
     * Create a new belongs to relationship instance.
     *
     * @param  string  $foreignKey
     * @param  string  $ownerKey
     * @param  string  $relationName
     * @param  bool  $isString
     * @return void
     */
    public function __construct(Builder $query, Model $child, $foreignKey, $ownerKey, $relationName, $isString = false)
    {
        $this->isString = $isString;
        parent::__construct($query, $child, $foreignKey, $ownerKey, $relationName);
    }

    /**
     * Set the base constraints on the relation query.
     *
     * @return void
     */
    public function addConstraints()
    {
        if (static::$constraints) {
            $query = $this->getBaseQuery();

            $query->when($this->isString, function ($q) {
                $q->whereJsonContains($this->ownerKey, (string) $this->getParentKey());
            }, function ($q) {
                $q->whereJsonContains($this->ownerKey, $this->getParentKey());
            });

            $query->whereNotNull($this->ownerKey);
        }
    }

    /**
     * Set the constraints for an eager load of the relation.
     *
     * @return void
     */
    public function addEagerConstraints(array $models)
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
     * @param  \Illuminate\Database\Eloquent\Collection  $results
     * @param  string  $relation
     * @return array
     */
    public function match(array $models, $results, $relation)
    {
        $owner = $this->getOwnerKeyName();
        foreach ($models as $model) {
            $id = $model->getAttribute($this->foreignKey);
            $collection = collect();
            foreach ($results as $data) {
                if (in_array($id, $data->{$owner})) {
                    $collection->push($data);
                }
            }
            $model->setRelation($relation, $collection);
        }

        return $models;
    }

    /**
     * Get the results of the relationship.
     *
     * @return mixed
     */
    public function getResults()
    {
        return $this->query->get();
    }
}
