<?php

namespace Mrpunyapal\LaravelExtendedRelationships\Relations;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Collection;

class HasManyArrayColumn extends HasMany
{
    /**
     * Set the base constraints on the relation query.
     *
     * @return void
     */
    public function addConstraints()
    {
        if (static::$constraints) {
            $query = $this->getRelationQuery();

            $query->wherein($this->foreignKey, $this->getParentKey());

            $query->whereNotNull($this->foreignKey);
        }
    }

    /**
     * Set the constraints for an eager load of the relation.
     *
     * @param  array  $models
     * @return void
     */
    public function addEagerConstraints(array $models)
    {
        $this->query->whereIn($this->foreignKey, $this->getKeys($models, $this->localKey));
    }

    /**
     * Get the Keys for an eager load of the relation.
     *
     * @param  array  $models
     * @param  string|null  $key
     * @return void
     */
    protected function getKeys(array $models, $key = null)
    {
        $keys = [];
        collect($models)->each(function ($value) use ($key, &$keys) {
            $keys = array_merge($keys, array_map('intval', ($value->getAttribute($key) ?? [])));
        });
        return array_unique($keys);
    }

    /**
     * Match the eagerly loaded results to their many parents.
     *
     * @param  array  $models
     * @param  \Illuminate\Database\Eloquent\Collection  $results
     * @param  string  $relation
     * @return array
     */
    public function matchMany(array $models, Collection $results, $relation)
    {

        $foreign = $this->getForeignKeyName();

        $dictionary = $results->mapToDictionary(function ($result) use ($foreign) {
            return [$result->{$foreign} => $result];
        })->all();

        foreach ($models as $model) {
            $ids = $model->getAttribute($this->localKey);
            $collection = collect();
            foreach ($ids ?? [] as $id) {
                if (isset($dictionary[$id]))
                    $collection = $collection->merge($this->getRelationValue($dictionary, $id, 'many'));
            }
            $model->setRelation($relation, $collection);
        }

        return $models;
    }
}
