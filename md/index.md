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