<?php

declare(strict_types=1);

namespace Mrpunyapal\LaravelExtendedRelationships\Relations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class HasManyKeys extends Relation
{
    /**
     * The foreign keys of the parent model.
     *
     * @var array<string>
     */
    protected array $foreignKeys;

    /**
     * The relations of the parent model.
     *
     * @var array<string>
     */
    protected array $relations;

    /**
     * Create a new has one or many relationship instance.
     */
    public function __construct(Builder $query, Model $parent, array $relations, protected string $localKey)
    {
        $this->foreignKeys = array_keys($relations);
        $this->relations = $relations;

        parent::__construct($query, $parent);
    }

    /**
     * Set the base constraints on the relation query.
     * Note: Used to load relations of one model.
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
     * Set the constraints for an eager load of the relation.
     * Note: Used to load relations of multiple models at once.
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
     * @param  string  $relation
     */
    public function initRelation(array $models, $relation): array
    {
        foreach ($models as $model) {
            $model->setRelation($relation, $this->related->newCollection());
        }

        return $models;
    }

    /**
     * Match the eagerly loaded results to their parents.
     * Info: From HasMany class.
     *
     * @param  string  $relation
     */
    public function match(array $models, Collection $results, $relation): array
    {
        $dictionary = $this->buildDictionary($results);

        foreach ($models as $model) {
            $key = $model->getAttribute($this->localKey);
            $desireRelations = json_decode('{}');
            foreach ($this->foreignKeys as $foreignKey) {
                if (isset($dictionary[$foreignKey][$key])) {
                    $desireRelations->{$this->relations[$foreignKey]} = $dictionary[$foreignKey][$key];
                }
            }
            $model->setRelation($relation, $desireRelations);
        }

        return $models;
    }

    /**
     * Build model dictionary keyed by the relation's foreign key.
     * Note: Custom code.
     */
    protected function buildDictionary(Collection $models): array
    {
        $dictionary = [];
        foreach ($models as $model) {
            foreach ($this->foreignKeys as $foreignKey) {
                $dictionary[$foreignKey][$model->{$foreignKey}] = $model;
            }
        }

        return $dictionary;
    }

    /**
     * Get the key value of the parent's local key.
     * Info: From HasOneOrMany class.
     */
    public function getParentKey(): mixed
    {
        return $this->parent->getAttribute($this->localKey);
    }

    /**
     * Get the results of the relationship.
     */
    public function getResults(): mixed
    {
        if (! static::$constraints) {
            return $this->get();
        }
        $results = $this->get();
        $desireResults = json_decode('{}');
        foreach ($this->foreignKeys as $foreignKey) {
            $desireResults->{$this->relations[$foreignKey]} = $results->where($foreignKey, '=', $this->getParentKey())
                ->whereNotNull($foreignKey);
        }

        return $desireResults;
    }
}
