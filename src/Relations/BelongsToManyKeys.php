<?php

declare(strict_types=1);

namespace MrPunyapal\LaravelExtendedRelationships\Relations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * @template TRelatedModel of \Illuminate\Database\Eloquent\Model
 * @template TDeclaringModel of \Illuminate\Database\Eloquent\Model
 *
 * @extends \Illuminate\Database\Eloquent\Relations\Relation<TRelatedModel, TDeclaringModel, object>
 */
class BelongsToManyKeys extends Relation
{
    /**
     * The local keys of the parent model.
     *
     * @var array<string>
     */
    protected array $localKeys;

    /**
     * The local keys of the parent model.
     *
     * @var array<string>
     */
    protected array $relations;

    /**
     * Create a new has one or many relationship instance.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<TRelatedModel>  $query
     * @param  TDeclaringModel  $parent
     * @param  string  $foreignKey
     * @param  array<string, string>  $relations
     */
    public function __construct(Builder $query, Model $parent, protected string $foreignKey, array $relations)
    {
        $this->localKeys = array_keys($relations);
        $this->relations = $relations;
        parent::__construct($query, $parent);
    }

    /**
     * Set the base constraints on the relation query.
     * Note: Used to load relations of one model.
     */
    public function addConstraints(): void
    {
        if (! static::$constraints) {
            return;
        }
        $this->query->where(function ($query): void {
            foreach ($this->localKeys as $localKey) {
                $query->orWhere(function ($query) use ($localKey): void {
                    $query->where($this->foreignKey, '=', $this->getParentKey($localKey))
                        ->whereNotNull($this->foreignKey);
                });
            }
        });
    }

    /**
     * Set the constraints for an eager load of the relation.
     * Note: Used to load relations of multiple models at once.
     */
    public function addEagerConstraints(array $models): void
    {
        $localKeys = $this->localKeys;
        $foreignKey = $this->foreignKey;
        $desireValues = [];
        foreach ($localKeys as $localKey) {
            $desireValues = array_merge($desireValues, $this->getKeys($models, $localKey));
        }
        $this->query->whereIn($foreignKey, array_filter(array_unique($desireValues)));
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
     * Match the related models with the given models based on the local keys.
     */
    public function match(array $models, Collection $results, $relation): array
    {
        $dictionary = $this->buildDictionary($results);

        foreach ($models as $model) {
            $desireRelations = json_decode('{}');
            foreach ($this->localKeys as $localKey) {
                $key = $model->getAttribute($localKey);
                if (isset($dictionary[$key])) {
                    $desireRelations->{$this->relations[$localKey]} = $dictionary[$key];
                }
            }
            $model->setRelation($relation, $desireRelations);
        }

        return $models;
    }

    /**
     * Build a dictionary using the given models.
     */
    public function buildDictionary(Collection $models): array
    {
        $dictionary = [];
        foreach ($models as $model) {
            $dictionary[$model->{$this->foreignKey}] = $model;
        }

        return $dictionary;
    }

    /**
     * Get the parent key value for the given local key.
     */
    public function getParentKey(string $localKey): mixed
    {
        return $this->parent->getAttribute($localKey);
    }

    /**
     * Get the results of the relationship.
     */
    public function getResults(): mixed
    {
        if (! static::$constraints) {
            return $this->query->get();
        }
        $results = $this->query->get();
        $desireResults = json_decode('{}');
        foreach ($this->localKeys as $localKey) {
            $desireResults->{$this->relations[$localKey]} = $results->where($this->foreignKey, '=', $this->getParentKey($localKey))->first();
        }

        return $desireResults;
    }
}
