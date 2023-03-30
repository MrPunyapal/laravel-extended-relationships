<?php

namespace Mrpunyapal\LaravelExtendedRelationships\Relations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class BelongsToManyWithManyKeys extends Relation
{
    /**
     * The local keys of the parent model.
     *
     * @var string[]
     */
    protected $localKeys;
    /**
     * The local keys of the parent model.
     *
     * @var string[]
     */
    protected $relations;

    /**
     * The foreign key of the related model.
     *
     * @var string
     */
    protected $foreignKey;

    /**
     * Create a new has one or many relationship instance.
     *
     * @param  Builder  $query
     * @param  Model  $parent
     * @param  string  $foreignKey
     * @param  array  $localKeys
     * @return void
     */
    public function __construct(Builder $query, Model $parent, string $foreignKey, array $relations)
    {
        $this->localKeys = array_keys($relations);
        $this->foreignKey = $foreignKey;
        $this->relations = $relations;
        parent::__construct($query, $parent);
    }

    /**
     * Set the base constraints on the relation query.
     * Note: Used to load relations of one model.
     *
     * @return void
     */
    public function addConstraints()
    {
        if (static::$constraints) {
            $localKeys = $this->localKeys;

            $this->query->where(function ($query) use ($localKeys) {
                foreach ($localKeys as $localKey) {
                    $query->orWhere(function ($query) use ($localKey) {
                        $query->where($this->foreignKey, '=', $this->getParentKey($localKey))
                            ->whereNotNull($this->foreignKey);
                    });
                }
            });
        }
    }

    /**
     * Set the constraints for an eager load of the relation.
     * Note: Used to load relations of multiple models at once.
     *
     * @param  array  $models
     */
    public function addEagerConstraints(array $models)
    {
        $localKeys = $this->localKeys;
        $foreignKey = $this->foreignKey;
        $desireValues = [];
        foreach ($localKeys as $localKey) {
            $desireValues = array_merge($desireValues, $this->getKeys($models, $localKey) ?? []);
        }
        $this->query->WhereIn($foreignKey, array_filter(array_unique($desireValues)));
    }

    /**
     * Initialize the relation on a set of models.
     *
     * @param  array   $models
     * @param  string  $relation
     * @return array
     */
    public function initRelation(array $models, $relation)
    {
        foreach ($models as $model) {
            $model->setRelation($relation, $this->related->newCollection());
        }

        return $models;
    }

    public function match(array $models, Collection $results, $relation)
    {
        $dictionary = $this->buildDictionary($results);

        // Once we have the dictionary we can simply spin through the parent models to
        // link them up with their children using the keyed dictionary to make the
        // matching very convenient and easy work. Then we'll just return them.
        foreach ($models as $model) {
            foreach ($this->localKeys as $localKey) {
                $key = $model->getAttribute($localKey);
                if (
                    isset($dictionary[$key])
                ) {
                    $model->setRelation($this->relations[$localKey], $dictionary[$key]);
                }
            }
        }
        $model->unsetRelation($relation);
        return $models;
    }


    public function buildDictionary(Collection $models)
    {

        $dictionary = [];
        foreach ($models as $model) {
            $dictionary[$model->{$this->foreignKey}] = $model;
        }
        return $dictionary;
    }


    public function getParentKey($localKey)
    {
        return $this->parent->getAttribute($localKey);
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
