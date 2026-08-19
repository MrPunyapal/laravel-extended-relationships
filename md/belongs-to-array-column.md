# BelongsToArrayColumn

`belongsToArrayColumn()` is the inverse of [`hasManyArrayColumn()`](has-many-array-column/): a model "belongs to" the rows whose array column contains its key.

## Signature

```php
public function belongsToArrayColumn(
    string $related,
    ?string $foreignKey,
    ?string $localKey,
    bool $isString = false,
): BelongsToArrayColumn
```

| Argument | Description |
| --- | --- |
| `$related` | The related model class (e.g. `User::class`) |
| `$foreignKey` | Your own primary key used to match inside the array (`id`) |
| `$localKey` | The other table's array column (e.g. `companies`) |
| `$isString` | Set to `true` when the array column holds string values (`"7"`, `"71"`) while your key is an integer |

## Example

A `Company` whose founders are the users whose `companies` array contains its id:

```php
<?php

use Illuminate\Database\Eloquent\Model;
use MrPunyapal\LaravelExtendedRelationships\HasExtendedRelationships;

class Company extends Model
{
    use HasExtendedRelationships;

    public function companyFounders()
    {
        return $this->belongsToArrayColumn(
            related: User::class,
            foreignKey: 'id',
            localKey: 'companies',
            // optional: true when the array stores stringified ids
            isString: true,
        );
    }
}
```

## Fetching related data

```php
$company = Company::with('companyFounders')->find(71);

// users whose companies column contains 71
$company->companyFounders;
```

The `isString` flag matters when the array column contains values such as `["7", "71"]` but your model's key is the integer `7` — without it the match would silently fail.