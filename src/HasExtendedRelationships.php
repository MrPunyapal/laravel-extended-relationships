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
    public function belongsToManyKeys(string $related, ?string $foreignKey, ?array $relations): BelongsToManyKeys
    {
        return new BelongsToManyKeys($this->relatedNewQuery($related), $this, $foreignKey, $relations);
    }

    public function hasManyKeys(string $related, ?array $relations, ?string $localKey): HasManyKeys
    {
        return new HasManyKeys($this->relatedNewQuery($related), $this, $relations, $localKey);
    }

    public function hasManyArrayColumn(string $related, ?string $foreignKey, ?string $localKey): HasManyArrayColumn
    {
        return new HasManyArrayColumn($this->relatedNewQuery($related), $this, $foreignKey, $localKey);
    }

    public function belongsToArrayColumn(string $related, ?string $foreignKey, ?string $localKey, bool $isString = false): BelongsToArrayColumn
    {
        return new BelongsToArrayColumn($this->relatedNewQuery($related), $this, $foreignKey, $localKey, null, $isString);
    }

    protected function relatedNewQuery($related): Builder
    {
        return (new $related)->newQuery();
    }
}
