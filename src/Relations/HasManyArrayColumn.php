<?php

declare(strict_types=1);

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
        if (! static::$constraints) {
            return;
        }

        $query = $this->getRelationQuery();

        $parentKeys = $this->getParentKey();
        if (! empty($parentKeys)) {
            $query->whereIn($this->foreignKey, $parentKeys);
        }

        $query->whereNotNull($this->foreignKey);
    }

    /**
     * Get the parent key(s) for the relationship.
     */
    public function getParentKey(): array
    {
        $attribute = $this->parent->getAttribute($this->localKey);

        return is_array($attribute) ? $attribute : [];
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
            $attribute = $value->getAttribute($key);
            if (is_array($attribute)) {
                $keys = array_merge($keys, $attribute);
            }
        });

        return array_values(array_unique($keys));
    }

    /**
     * Match the eagerly loaded results to their many parents.
     *
     * @param  string  $relation
     */
    public function matchMany(array $models, Collection $results, $relation): array
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
