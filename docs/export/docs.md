# BelongsToArrayColumn

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

---

# BelongsToManyKeys

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

---

# HasManyArrayColumn

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

---

# HasManyKeys

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

---

# Installation

# Installation

## Requirements

- PHP `^8.2`, `^8.3`, `^8.4`, or `^8.5`
- Laravel 11, 12, or 13

## Install the package

```bash
composer require mrpunyapal/laravel-extended-relationships
```

The trait is all you use at runtime — there is nothing to configure. The package ships a small (currently empty) config file for future options, but it is optional and never required.

## Laravel Boost

The package ships a Laravel Boost skill named `laravel-extended-relationships-development` for on-demand guidance when working with the custom relationship helpers.

If your Laravel application uses Boost, install Boost and publish its resources:

```bash
composer require laravel/boost --dev
php artisan boost:install
```

If Boost is already installed and you add this package later, discover the new package skills:

```bash
php artisan boost:update --discover
```

## Next step

Add the `HasExtendedRelationships` trait to a model and define your first extended relationship — see [Usage](usage/).

---

# Laravel Extended Relationships

# Laravel Extended Relationships

More efficient Eloquent relationship methods for your Laravel models — fewer queries, less duplicated code.

The package ships a `HasExtendedRelationships` trait with four relationship builders that collapse multiple conventional relationships into a single, efficient query:

| Relationship | What it does |
| --- | --- |
| [BelongsToManyKeys](belongs-to-many-keys/) | Define several `belongsTo`-style relations to the same related model in one method |
| [HasManyKeys](has-many-keys/) | Inverse of `BelongsToManyKeys` — several `hasMany`-style relations with a single query |
| [HasManyArrayColumn](has-many-array-column/) | `hasMany` against a local column storing an array of foreign keys |
| [BelongsToArrayColumn](belongs-to-array-column/) | Inverse of `HasManyArrayColumn` — a model that belongs to entries in an array column |

## Why?

Eloquent gives you `belongsTo` and `hasMany`, but when the same related model is referenced by several keys — for example `created_by`, `updated_by`, and `deleted_by` all pointing at `users` — you end up writing three nearly identical relationships that fire three queries. These helpers express that pattern in one method that resolves with a **single** database query.

## Getting started

1. Install the package with Composer (see [Installation](installation/))
2. Add the `HasExtendedRelationships` trait to your model
3. Call the relationship method that fits your data shape (see [Usage](usage/))

---

# Usage

# Usage

Add the `HasExtendedRelationships` trait to your model:

```php
<?php

use Illuminate\Database\Eloquent\Model;
use MrPunyapal\LaravelExtendedRelationships\HasExtendedRelationships;

class Post extends Model
{
    use HasExtendedRelationships;

    // ...
}
```

Then pick the relationship method that matches your data shape:

| Method | Use when |
| --- | --- |
| [belongsToManyKeys()](belongs-to-many-keys/) | A row references the same related table through several foreign keys (`created_by`, `updated_by`, `deleted_by`) |
| [hasManyKeys()](has-many-keys/) | The inverse — the related table references back through several foreign keys |
| [hasManyArrayColumn()](has-many-array-column/) | A local column holds an array of foreign keys (`companies = [7, 71]`) |
| [belongsToArrayColumn()](belongs-to-array-column/) | The inverse — you belong to rows whose array column contains your key |

All four are used exactly like a normal Eloquent relationship: eager load with `with(...)`, lazy load by accessing the property, and they work with query scopes (`whereHas(...)`, `has(...)`, ...).

Every helper resolves all its relations in **one** database query — even when several relation names are declared.

```php
$post = Post::with('auditors')->first();

// created_by → creator
$post->auditors->creator;

// updated_by → updater
$post->auditors->updater;

// deleted_by → deleter
$post->auditors->deleter;
```
