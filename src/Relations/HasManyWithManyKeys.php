<?php

declare(strict_types=1);

namespace Mrpunyapal\LaravelExtendedRelationships\Relations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class HasManyWithManyKeys extends Relation
{

    /**
     * The foreign keys of the parent model.
     *
     * @var string[]
     */
    protected $foreignKeys;

    /**
     * The foreign keys of the parent model.
     *
     * @var string[]
     */
    protected $relations;

    /**
     * The local key of the parent model.
     *
     * @var string
     */
    protected $localKey;

    /**
     * Create a new has one or many relationship instance.
     *
     * @param  Builder  $query
     * @param  Model  $parent
     * @param  array  $relations
     * @param  string  $localKey
     * @return void
     */
    public function __construct(Builder $query, Model $parent, array $relations, string $localKey)
    {
        $this->foreignKeys = array_keys($relations);
        $this->relations = $relations;
        $this->localKey = $localKey;

        parent::__construct($query, $parent);
    }

    /**
     * Set the base constraints on the relation query.
     * Note: Used to load relations of one model.
     *
     * @return void
     */
    public function addConstraints(): void
    {
        if (static::$constraints) {
            $foreignKeys = $this->foreignKeys;

            $this->query->where(function ($query) use ($foreignKeys): void {
                foreach ($foreignKeys as $foreignKey) {
                    $query->orWhere(function ($query) use ($foreignKey): void {
                        $query->where($foreignKey, '=', $this->getParentKey())
                            ->whereNotNull($foreignKey);
                    });
                }
            });
        }
    }

    /**
     * Get the key value of the parent's local key.
     * Info: From HasOneOrMany class.
     *
     * @return mixed
     */
    public function getParentKey()
    {
        return $this->parent->getAttribute($this->localKey);
    }

    /**
     * Set the constraints for an eager load of the relation.
     * Note: Used to load relations of multiple models at once.
     *
     * @param  array  $models
     */
    public function addEagerConstraints(array $models): void
    {
        $foreignKeys = $this->foreignKeys;
        $this->query->where(function ($query) use ($foreignKeys, $models): void {
            foreach ($foreignKeys as $foreignKey) {
                $query->orWhereIn($foreignKey, $this->getKeys($models, $this->localKey));
            }
        });
    }

    /**
     * Initialize the relation on a set of models.
     *
     * @param  array  $models
     * @param  string  $relation
     * @return array
     */
    public function initRelation(array $models, $relation)
    {
        // Info: From HasMany class
        foreach ($models as $model) {
            $model->setRelation($relation, $this->related->newCollection());
        }

        return $models;
    }

    /**
     * Match the eagerly loaded results to their parents.
     * Info: From HasMany class.
     *
     * @param  array  $models
     * @param  Collection  $results
     * @param  string  $relation
     * @return array
     */
    public function match(array $models, Collection $results, $relation)
    {
        $dictionary = $this->buildDictionary($results);
        // dd($dictionary);
        // Once we have the dictionary we can simply spin through the parent models to
        // link them up with their children using the keyed dictionary to make the
        // matching very convenient and easy work. Then we'll just return them.
        foreach ($models as $model) {
            $key = $model->getAttribute($this->localKey);
            foreach ($this->foreignKeys as $foreignKey) {
                if (isset($dictionary[$foreignKey][$key])) {
                    $model->setRelation($this->relations[$foreignKey], $dictionary[$foreignKey][$key]);
                }
            }
            $model->unsetRelation($relation);
        }
        return $models;
    }

    /**
     * Build model dictionary keyed by the relation's foreign key.
     * Note: Custom code.
     *
     * @param  Collection  $results
     * @return array
     */
    protected function buildDictionary(Collection $models): array
    {
        // dd($models);
        $dictionary = [];
        foreach ($models as $model) {
            foreach ($this->foreignKeys as $foreignKey) {
                $dictionary[$foreignKey][$model->{$foreignKey}] = $model;
            }
        }
        return $dictionary;
    }

    /**
     * Get the results of the relationship.
     *
     * @return mixed
     */
    public function getResults()
    {
        return $this->get();
    }
}