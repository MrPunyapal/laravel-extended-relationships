<?php

namespace Mrpunyapal\LaravelExtendedRelationships\Relations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class BelongsToManyKeys extends Relation
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
            $this->query->where(function ($query) {
                foreach ($this->localKeys as $localKey) {
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
        $this->query->whereIn($foreignKey, array_filter(array_unique($desireValues)));
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

        foreach ($models as $model) {
            $desireRelations = json_decode('{}');
            foreach ($this->localKeys as $localKey) {
                $key = $model->getAttribute($localKey);
                if (isset($dictionary[$key]))
                    $desireRelations->{$this->relations[$localKey]} = $dictionary[$key];
            }
            $model->setRelation($relation, $desireRelations);
        }
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
        if (!static::$constraints) {
            return $this->query->get();;
        }
        $results = $this->query->get();
        $desireResults = json_decode('{}');
        foreach ($this->localKeys as $localKey) {
            $desireResults->{$this->relations[$localKey]} = $results->where($this->foreignKey, '=', $this->getParentKey($localKey))->first();
        }
        return  $desireResults;
    }
}
