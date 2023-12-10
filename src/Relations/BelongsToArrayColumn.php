<?php

namespace Mrpunyapal\LaravelExtendedRelationships\Relations;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BelongsToArrayColumn extends BelongsTo
{
    /**
     * Set the base constraints on the relation query.
     *
     * @return void
     */
    public function addConstraints()
    {
        if (static::$constraints) {
            $query = $this->getBaseQuery();

            $query->whereJsonContains($this->ownerKey, $this->getParentKey())
                ->orWhereJsonContains($this->ownerKey, $this->getParentKey().'');

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
                $q->orWhereJsonContains($this->ownerKey, $id)
                    ->orWhereJsonContains($this->ownerKey, $id.'');
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
