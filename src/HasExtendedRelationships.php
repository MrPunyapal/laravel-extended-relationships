<?php

namespace Mrpunyapal\LaravelExtendedRelationships;

use Mrpunyapal\LaravelExtendedRelationships\Relations\BelongsToManyWithManyKeys;
use Mrpunyapal\LaravelExtendedRelationships\Relations\HasManyWithManyKeys;
use Mrpunyapal\LaravelExtendedRelationships\Relations\HasManyWithColumnKeyArray;

trait HasExtendedRelationships
{
    /**
     * @param  string  $related
     * @param  string|null  $foreignKey
     * @param  string[]|null  $relations
     * @return BelongsToManyWithManyKeys
     */
    public function belongsToManyWithManyKeys(string $related, string $foreignKey, array $relations): BelongsToManyWithManyKeys
    {
        $instance = new $related();
        return new BelongsToManyWithManyKeys($instance->newQuery(), $this, $foreignKey, $relations);
    }

    /**
     * @param  string  $related
     * @param  string[]|null  $relations
     * @param  string|null  $localKey
     * @return HasManyWithManyKeys
     */
    public function hasManyWithManyKeys(string $related, ?array $relations = null, ?string $localKey = null): HasManyWithManyKeys
    {
        $instance = new $related();
        return new HasManyWithManyKeys($instance->newQuery(), $this, $relations, $localKey);
    }
    
      /**
     * @param  string  $related
     * @param  string|null  $localKey
     * @param  string|null  $foreignKey
     * @return HasManyWithColumnKeyArray
     */
    public function hasManyWithColumnKeyArray(string $related, ?string $foreignKey, ?string $localKey):HasManyWithColumnKeyArray
    {
        $instance = new $related();
        return new HasManyWithColumnKeyArray($instance->newQuery(), $this, $foreignKey, $localKey);
    }

}
