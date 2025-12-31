<?php

declare(strict_types=1);

namespace MrPunyapal\LaravelExtendedRelationships;

use Illuminate\Database\Eloquent\Builder;
use MrPunyapal\LaravelExtendedRelationships\Relations\BelongsToArrayColumn;
use MrPunyapal\LaravelExtendedRelationships\Relations\BelongsToManyKeys;
use MrPunyapal\LaravelExtendedRelationships\Relations\HasManyArrayColumn;
use MrPunyapal\LaravelExtendedRelationships\Relations\HasManyKeys;

trait HasExtendedRelationships
{
    /**
     * Define a belongs-to-many-keys relationship.
     *
     * @template TRelatedModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  class-string<TRelatedModel>  $related
     * @param  string|null  $foreignKey
     * @param  array<string, string>|null  $relations
     * @return \MrPunyapal\LaravelExtendedRelationships\Relations\BelongsToManyKeys<TRelatedModel, $this>
     */
    public function belongsToManyKeys(string $related, ?string $foreignKey, ?array $relations): BelongsToManyKeys
    {
        return new BelongsToManyKeys($this->relatedNewQuery($related), $this, $foreignKey, $relations);
    }

    /**
     * Define a has-many-keys relationship.
     *
     * @template TRelatedModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  class-string<TRelatedModel>  $related
     * @param  array<string, string>|null  $relations
     * @param  string|null  $localKey
     * @return \MrPunyapal\LaravelExtendedRelationships\Relations\HasManyKeys<TRelatedModel, $this>
     */
    public function hasManyKeys(string $related, ?array $relations, ?string $localKey): HasManyKeys
    {
        return new HasManyKeys($this->relatedNewQuery($related), $this, $relations, $localKey);
    }

    /**
     * Define a has-many-array-column relationship.
     *
     * @template TRelatedModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  class-string<TRelatedModel>  $related
     * @param  string|null  $foreignKey
     * @param  string|null  $localKey
     * @return \MrPunyapal\LaravelExtendedRelationships\Relations\HasManyArrayColumn<TRelatedModel, $this>
     */
    public function hasManyArrayColumn(string $related, ?string $foreignKey, ?string $localKey): HasManyArrayColumn
    {
        return new HasManyArrayColumn($this->relatedNewQuery($related), $this, $foreignKey, $localKey);
    }

    /**
     * Define a belongs-to-array-column relationship.
     *
     * @template TRelatedModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  class-string<TRelatedModel>  $related
     * @param  string|null  $foreignKey
     * @param  string|null  $localKey
     * @param  bool  $isString
     * @return \MrPunyapal\LaravelExtendedRelationships\Relations\BelongsToArrayColumn<TRelatedModel, $this>
     */
    public function belongsToArrayColumn(string $related, ?string $foreignKey, ?string $localKey, bool $isString = false): BelongsToArrayColumn
    {
        return new BelongsToArrayColumn($this->relatedNewQuery($related), $this, $foreignKey, $localKey, null, $isString);
    }

    /**
     * Create a new query for the related model.
     *
     * @template TRelatedModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  class-string<TRelatedModel>  $related
     * @return \Illuminate\Database\Eloquent\Builder<TRelatedModel>
     */
    protected function relatedNewQuery(string $related): Builder
    {
        return (new $related)->newQuery();
    }
}
