<?php

namespace Mrpunyapal\LaravelExtendedRelationships;

use Mrpunyapal\LaravelExtendedRelationships\Relations\BelongsToManyWithManyKeys;
use Mrpunyapal\LaravelExtendedRelationships\Relations\HasManyWithManyKeys;

trait LaravelExtendedRelationships
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
     * @param  string[]|null  $foreignKeys
     * @param  string|null  $localKey
     * @return HasManyWithManyKeys
     */
    public function hasManyWithManyKeys(string $related, ?array $relations = null, ?string $localKey = null): HasManyWithManyKeys
    {
        $instance = new $related();
        return new HasManyWithManyKeys($instance->newQuery(), $this, $relations, $localKey);
    }
}
