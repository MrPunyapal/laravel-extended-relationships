<?php

namespace Mrpunyapal\LaravelExtendedRelationships;

use Mrpunyapal\LaravelExtendedRelationships\Relations\BelongsToArrayColumn;
use Mrpunyapal\LaravelExtendedRelationships\Relations\BelongsToManyKeys;
use Mrpunyapal\LaravelExtendedRelationships\Relations\HasManyKeys;
use Mrpunyapal\LaravelExtendedRelationships\Relations\HasManyArrayColumn;

trait HasExtendedRelationships
{
    /**
     * @param  string  $related
     * @param  string|null  $foreignKey
     * @param  string[]|null  $relations
     * @return BelongsToManyKeys
     */
    public function belongsToManyKeys(string $related, string $foreignKey, array $relations): BelongsToManyKeys
    {
        $instance = new $related();
        return new BelongsToManyKeys($instance->newQuery(), $this, $foreignKey, $relations);
    }

    /**
     * @param  string  $related
     * @param  string[]|null  $relations
     * @param  string|null  $localKey
     * @return HasManyKeys
     */
    public function hasManyKeys(string $related, ?array $relations = null, ?string $localKey = null): HasManyKeys
    {
        $instance = new $related();
        return new HasManyKeys($instance->newQuery(), $this, $relations, $localKey);
    }
    
      /**
     * @param  string  $related
     * @param  string|null  $localKey
     * @param  string|null  $foreignKey
     * @return HasManyArrayColumn
     */
    public function hasManyArrayColumn(string $related, ?string $foreignKey, ?string $localKey):HasManyArrayColumn
    {
        $instance = new $related();
        return new HasManyArrayColumn($instance->newQuery(), $this, $foreignKey, $localKey);
    }

    /**
     * @param  string  $related
     * @param  string|null  $localKey
     * @param  string|null  $foreignKey
     * @return BelongsToArrayColumn
     */
    public function belongsToArrayColumn(string $related, ?string $foreignKey, ?string $localKey): BelongsToArrayColumn
    {
        $instance = new $related();
        return new BelongsToArrayColumn($instance->newQuery(), $this, $foreignKey, $localKey, null);
    }

}
