# HasManyKeys

`hasManyKeys()` is the inverse of [`belongsToManyKeys()`](belongs-to-many-keys/): instead of a model that knows its related rows through several foreign keys, it gives the *related* model several `hasMany`-style collections — again resolved with one query.

## Signature

```php
public function hasManyKeys(
    string $related,
    ?array $relations,
    ?string $localKey,
): HasManyKeys
```

| Argument | Description |
| --- | --- |
| `$related` | The related model class (e.g. `Post::class`) |
| `$relations` | Map of the related table's foreign keys to relation names, e.g. `['created_by' => 'created', 'updated_by' => 'updated']` |
| `$localKey` | The local column used to match (`id`) |

## Example

A `User` that wants all posts they created, updated, and deleted:

```php
<?php

use Illuminate\Database\Eloquent\Model;
use MrPunyapal\LaravelExtendedRelationships\HasExtendedRelationships;

class User extends Model
{
    use HasExtendedRelationships;

    public function audited()
    {
        return $this->hasManyKeys(
            related: Post::class,
            relations: [
                'created_by' => 'created',
                'updated_by' => 'updated',
                'deleted_by' => 'deleted',
            ],
            localKey: 'id',
        );
    }
}
```

## Fetching related data

Eager loading:

```php
$user = User::with('audited')->first();

$user->audited->created;
$user->audited->updated;
$user->audited->deleted;
```

Lazy loading works the same way:

```php
$user = User::find(71);

$user->audited->created;
$user->audited->updated;
$user->audited->deleted;
```

Multiple collections are populated in a single query, keeping your N+1 count at zero.