---
name: laravel-extended-relationships-development
description: "Use this skill when working with MrPunyapal Laravel Extended Relationships package. Activate when adding or fixing `belongsToManyKeys`, `hasManyKeys`, `hasManyArrayColumn`, or `belongsToArrayColumn` relationships, or when a Laravel model stores multiple related keys in columns or JSON array columns. Covers trait setup, relationship definitions, eager and lazy loading, inverse relationship patterns, and array-string matching. Do not use for standard Eloquent relationships that Laravel already supports directly."
license: MIT
metadata:
  author: mrpunyapal
---

# Laravel Extended Relationships Development

## Overview

`mrpunyapal/laravel-extended-relationships` adds focused Eloquent relationship helpers for models that:

- map several foreign-key columns to named related models through one relation,
- map several foreign-key columns back to grouped inverse relations,
- read related models from an array column of keys,
- find parent models whose array column contains the current model key.

Use the package trait instead of hand-rolling custom relationship queries for those cases.

## Setup

Add the trait to every model that defines these relationships:

```php
use Illuminate\Database\Eloquent\Model;
use MrPunyapal\LaravelExtendedRelationships\HasExtendedRelationships;

class Post extends Model
{
    use HasExtendedRelationships;
}
```

The package exposes four methods from the trait:

- `belongsToManyKeys(string $related, ?string $foreignKey, ?array $relations)`
- `hasManyKeys(string $related, ?array $relations, ?string $localKey)`
- `hasManyArrayColumn(string $related, ?string $foreignKey, ?string $localKey)`
- `belongsToArrayColumn(string $related, ?string $foreignKey, ?string $localKey, bool $isString = false)`

## Multiple belongs-to columns

Use `belongsToManyKeys` when one model has multiple foreign-key columns that all point to the same related model.

```php
use App\Models\User;

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
```

The `relations` array maps source columns on the current model to the property names you want to read from the loaded relationship object.

Use it like this:

```php
$post = Post::with('auditors')->first();

$post->auditors->creator;
$post->auditors->updater;
$post->auditors->deleter;
```

Lazy loading is also supported:

```php
$post = Post::find(7);

$post->auditors->creator;
```

## Multiple inverse has-many columns

Use `hasManyKeys` for the inverse side when the related model stores several foreign-key columns that can all reference the current model.

```php
use App\Models\Post;

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
```

Read the grouped inverse collections from the returned relation object:

```php
$user = User::with('audited')->first();

$user->audited->created;
$user->audited->updated;
$user->audited->deleted;
```

## Array column to many models

Use `hasManyArrayColumn` when the current model stores an array of related keys in one column.

Typical example: a `users.companies` JSON/array column stores `[7, 71]`.

```php
use App\Models\Company;

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
```

```php
$user = User::with('myCompanies')->first();

$user->myCompanies;
```

## Inverse lookup from an array column

Use `belongsToArrayColumn` when the related model stores an array column and you need to find all records whose array contains the current model key.

```php
use App\Models\User;

public function companyFounders()
{
    return $this->belongsToArrayColumn(
        related: User::class,
        foreignKey: 'id',
        localKey: 'companies',
        isString: true,
    );
}
```

```php
$company = Company::with('companyFounders')->find(71);

$company->companyFounders;
```

Set `isString: true` when the array column stores string values like `"7"` and `"71"` instead of integers. Leave it `false` for numeric arrays.

## Implementation guidance

- Add `use HasExtendedRelationships;` before defining any extended relationship methods.
- Keep relation names descriptive because consumers read grouped results from dynamic properties like `$post->auditors->creator`.
- Cast JSON-backed array columns to `array` on the model before using `hasManyArrayColumn` or `belongsToArrayColumn`.
- Prefer eager loading when a page or endpoint needs these grouped relationships for multiple models.
- Use normal Eloquent relationships when a standard `belongsTo`, `hasMany`, or `belongsToMany` already matches the data shape.

## Verification

After adding or changing one of these relationships:

1. Eager load the relationship in a test with `Model::with('relation')->first()`.
2. Assert each mapped property returns the expected model or collection.
3. Cover both integer-array and string-array behavior when using `belongsToArrayColumn(..., isString: true)`.
4. Run the package test suite with `composer test`.

## Common pitfalls

- Forgetting the trait on the model that defines the custom relationship.
- Swapping the source column names and exposed relation names inside the `relations` array.
- Using array-column relationships without casting the backing attribute to `array`.
- Omitting `isString: true` when the array column stores stringified IDs.
- Replacing standard Eloquent relations with this package when the schema does not actually require it.
