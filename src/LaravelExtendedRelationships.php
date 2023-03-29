<?php

namespace Mrpunyapal\LaravelExtendedRelationships;

use Mrpunyapal\LaravelExtendedRelationships\Relations\BelongsToManyMerged;

trait LaravelExtendedRelationships
{
    public function belongsToManyMerged(string $related, string $foreignKey, array $relations): BelongsToManyMerged
    {
        $instance = new $related();
        return new BelongsToManyMerged($instance->newQuery(), $this, $foreignKey, $relations);
    }
}
