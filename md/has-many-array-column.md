# HasManyArrayColumn

`hasManyArrayColumn()` defines a `hasMany` relationship where a **local column stores an array of foreign keys**, rather than a single value.

## Signature

```php
public function hasManyArrayColumn(
    string $related,
    ?string $foreignKey,
    ?string $localKey,
): HasManyArrayColumn
```

| Argument | Description |
| --- | --- |
| `$related` | The related model class (e.g. `Company::class`) |
| `$foreignKey` | The primary key of the related table (`id`) |
| `$localKey` | The local array column containing related keys (`companies`) |

## Example

A `User` whose `companies` column stores an array of company IDs like `[7, 71]`:

```php
<?php

use Illuminate\Database\Eloquent\Model;
use MrPunyapal\LaravelExtendedRelationships\HasExtendedRelationships;

class User extends Model
{
    use HasExtendedRelationships;

    protected $casts = [
        'companies' => 'array',
    ];

    public function myCompanies()
    {
        return $this->hasManyArrayColumn(
            related: Company::class,
            foreignKey: 'id',
            localKey: 'companies',
        );
    }
}
```

## Fetching related data

```php
$user = User::with('myCompanies')->first();

// companies with ids 7 and 71
$user->myCompanies;
```

No pivot table and no extra query per company — the relation resolves all stored IDs with a single query.