<?php

namespace Mrpunyapal\LaravelExtendedRelationships;

use Mrpunyapal\LaravelExtendedRelationships\Relations\BelongsToArrayColumn;
use Mrpunyapal\LaravelExtendedRelationships\Relations\BelongsToManyKeys;
use Mrpunyapal\LaravelExtendedRelationships\Relations\HasManyArrayColumn;
use Mrpunyapal\LaravelExtendedRelationships\Relations\HasManyKeys;

trait HasExtendedRelationships
{
    /**
     * @param  string[]|null  $relations
     */
    public function belongsToManyKeys(string $related, ?string $foreignKey, ?array $relations): BelongsToManyKeys
    {
        return new BelongsToManyKeys((new $related())->newQuery(), $this, $foreignKey, $relations);
    }

    /**
     * @param  string[]|null  $relations
     */
    public function hasManyKeys(string $related, ?array $relations, ?string $localKey): HasManyKeys
    {
        return new HasManyKeys((new $related())->newQuery(), $this, $relations, $localKey);
    }

    public function hasManyArrayColumn(string $related, ?string $foreignKey, ?string $localKey): HasManyArrayColumn
    {
        return new HasManyArrayColumn((new $related())->newQuery(), $this, $foreignKey, $localKey);
    }

    public function belongsToArrayColumn(string $related, ?string $foreignKey, ?string $localKey, bool $isString = false): BelongsToArrayColumn
    {
        return new BelongsToArrayColumn((new $related())->newQuery(), $this, $foreignKey, $localKey, null, $isString);
    }
}
