# BelongsToManyKeys

`belongsToManyKeys()` defines several `belongsTo`-style relations to the same related table through a single relationship — and a single database query.

## Signature

```php
public function belongsToManyKeys(
    string $related,
    ?string $foreignKey,
    ?array $relations,
): BelongsToManyKeys
```

| Argument | Description |
| --- | --- |
| `$related` | The related model class (e.g. `User::class`) |
| `$foreignKey` | The column on the related table used to match (`id`) |
| `$relations` | Map of the related table's foreign keys to relation names, e.g. `['created_by' => 'creator', 'updated_by' => 'updater']` |

## Example

A `Post` that is created, updated, and deleted by different users:

```php
<?php

use Illuminate\Database\Eloquent\Model;
use MrPunyapal\LaravelExtendedRelationships\HasExtendedRelationships;

class Post extends Model
{
    use HasExtendedRelationships;

    public function auditors()
    {
        return $this->belongsToManyKeys(
            related: User::class,
            foreignKey: 'id',
            relations: [
                'created_by' => 'creator',
                'updated_by' => 'updater',
                'deleted_by' => 'deleter',
            ],
        );
    }
}
```

## Fetching related data

Eager loading:

```php
$post = Post::with('auditors')->first();

$post->auditors->creator;
$post->auditors->updater;
$post->auditors->deleter;
```

Lazy loading works the same way:

```php
$post = Post::find(7);

$post->auditors->creator;
$post->auditors->updater;
$post->auditors->deleter;
```

The relationship exposes every key in the map as a relation on the returned object — three classic `belongsTo` relations, but fired with a **single** query.