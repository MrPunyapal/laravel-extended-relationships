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