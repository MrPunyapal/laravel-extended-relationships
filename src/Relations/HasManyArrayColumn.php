<?php

namespace Mrpunyapal\LaravelExtendedRelationships\Relations;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HasManyArrayColumn extends HasMany
{
    /**
     * Set the base constraints on the relation query.
     */
    public function addConstraints(): void
    {
        if (static::$constraints) {
            $query = $this->getRelationQuery();

            $query->wherein($this->foreignKey, $this->getParentKey());

            $query->whereNotNull($this->foreignKey);
        }
    }

    /**
     * Set the constraints for an eager load of the relation.
     */
    public function addEagerConstraints(array $models): void
    {
        $this->query->whereIn($this->foreignKey, $this->getKeys($models, $this->localKey));
    }

    /**
     * Get the Keys for an eager load of the relation.
     *
     * @param  string|null  $key
     */
    protected function getKeys(array $models, $key = null): array
    {
        $keys = [];
        collect($models)->each(function ($value) use ($key, &$keys) {
            $keys = array_merge($keys, $value->getAttribute($key));
        });

        return array_unique($keys);
    }

    /**
     * Match the eagerly loaded results to their many parents.
     *
     * @param  string  $relation
     */
    public function matchMany(array $models, Collection $results, $relation): array|Collection
    {
        $foreign = $this->getForeignKeyName();

        $dictionary = $results->mapToDictionary(fn ($result) => [$result->{$foreign} => $result])->all();

        foreach ($models as $model) {
            $ids = $model->getAttribute($this->localKey);
            $collection = collect();
            foreach ($ids ?? [] as $id) {
                if (isset($dictionary[$id])) {
                    $collection = $collection->merge($this->getRelationValue($dictionary, $id, 'many'));
                }
            }
            $model->setRelation($relation, $collection);
        }

        return $models;
    }
}
