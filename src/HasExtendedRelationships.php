<?php

namespace Mrpunyapal\LaravelExtendedRelationships;

use Mrpunyapal\LaravelExtendedRelationships\Relations\BelongsToArrayColumn;
use Mrpunyapal\LaravelExtendedRelationships\Relations\BelongsToManyKeys;
use Mrpunyapal\LaravelExtendedRelationships\Relations\HasManyArrayColumn;
use Mrpunyapal\LaravelExtendedRelationships\Relations\HasManyKeys;

trait HasExtendedRelationships
{
    /**
     * @param  string|null  $foreignKey
     * @param  string[]|null  $relations
     */
    public function belongsToManyKeys(string $related, string $foreignKey, array $relations): BelongsToManyKeys
    {
        $instance = new $related();

        return new BelongsToManyKeys($instance->newQuery(), $this, $foreignKey, $relations);
    }

    /**
     * @param  string[]|null  $relations
     */
    public function hasManyKeys(string $related, ?array $relations = null, ?string $localKey = null): HasManyKeys
    {
        $instance = new $related();

        return new HasManyKeys($instance->newQuery(), $this, $relations, $localKey);
    }

    public function hasManyArrayColumn(string $related, ?string $foreignKey, ?string $localKey): HasManyArrayColumn
    {
        $instance = new $related();

        return new HasManyArrayColumn($instance->newQuery(), $this, $foreignKey, $localKey);
    }

    public function belongsToArrayColumn(string $related, ?string $foreignKey, ?string $localKey, $isString = false): BelongsToArrayColumn
    {
        $instance = new $related();

        return new BelongsToArrayColumn($instance->newQuery(), $this, $foreignKey, $localKey, null, $isString);
    }
}
