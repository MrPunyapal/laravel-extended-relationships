# laravel-extended-relationships

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mr-punyapal/laravel-extended-relationships.svg?style=flat-square)](https://packagist.org/packages/mr-punyapal/laravel-extended-relationships)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/mr-punyapal/laravel-extended-relationships/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/mr-punyapal/laravel-extended-relationships/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/mr-punyapal/laravel-extended-relationships.svg?style=flat-square)](https://packagist.org/packages/mr-punyapal/laravel-extended-relationships)

### What is a need of extended relationships?
The laravel-extended-relationships package provides additional, more efficient relationship methods for Laravel Eloquent models. The package offers several useful features such as reducing the number of database queries, improving performance, and minimizing duplicate code.
  
I faced issue and made my own relationships then realize if I can use packages from open source then I can make one too and made this package.

## Installation

You can install the package via composer:

```bash
composer require mrpunyapal/laravel-extended-relationships
```

## Usage

First, include the `HasExtendedRelationships` trait in your model:

```php

use MrPunyapal\LaravelExtendedRelationships\HasExtendedRelationships;

class Post extends Model {
    use HasExtendedRelationships;

    //...
}

```

Next, define the `BelongsToManyKeys` relationship with the `belongsToManyKeys` method:

```php

public function auditors() {
    return $this->belongsToManyKeys(
        related: User::class,
        foreignKey: 'id',
        relations: [
            'created_by' => 'creator',
            'updated_by' => 'updater',
            'deleted_by' => 'deleter',
        ]
    );
}

```

### This method takes three arguments:

* The related model (`User::class`)
* The foreign key (`id`)
* An array mapping the related table's foreign key names to the corresponding relation names on the model (`['created_by' => 'creator', ...]`)

### Then, you can fetch data from the auditors relationship like so:

```php

$post = Post::with('auditors')->first();

// Get the creator
$post->auditors->creator;

// Get the updater
$post->auditors->updater;

// Get the deleter
$post->auditors->deleter;


// also works with lazy loading

$post = Post::find(7);

// Get the creator
$post->auditors->creator;

// Get the updater
$post->auditors->updater;

// Get the deleter
$post->auditors->deleter;

```

This allows you to define multiple relationships with just one method, and only a single query is fired in the database for all the relationships.



### Inverse relationship.


```php

use MrPunyapal\LaravelExtendedRelationships\HasExtendedRelationships;

class User extends Model{

    use HasExtendedRelationships;

    public function audited(){
        return $this->hasManyKeys(
            related: Post::class,
            relations: [
                'created_by' => 'created', 
                'updated_by' => 'updated', 
                'deleted_by' => 'deleted',
            ],
            localKey: 'id'
        );
    }
}

```

To retrieve the audited posts of a user, you can use the audited relationship. Here's an example:

```php

$user = User::with('audited')->first();

// Get posts created by the user
$user->audited->created;

// Get posts updated by the user
$user->audited->updated;

// Get posts deleted by the user
$user->audited->deleted;

// also works with lazy loading

$user = User::find(71);

// Get posts created by the user
$user->audited->created;

// Get posts updated by the user
$user->audited->updated;

// Get posts deleted by the user
$user->audited->deleted;

```

This allows you to define multiple relationships between models with a single method call, simplifying your code and reducing the number of queries executed.

### HasManyArrayColumn

If you have a column companies in your users table which stores an array of local keys like [7, 71] or ["7", "71"], you can use the following relationship:

```php 

use MrPunyapal\LaravelExtendedRelationships\HasExtendedRelationships;

class User extends Model
{
    use HasExtendedRelationships;

    protected $casts=[
       'companies' => 'array'
    ];

    public function myCompanies()
    {
        return $this->hasManyArrayColumn(
            related: Company::class,
            foreignKey: 'id',
            localKey: 'companies'
        );
    }
}

```

When fetching data, you can retrieve the related companies with:

```php

$user = User::with('myCompanies')->first();

// get companies with ids 7 and 71
$user->myCompanies;

```

This allows you to easily retrieve related records with an array of local keys, which can be useful in certain scenarios.

### Inverse Relationship for `HasManyArrayColumn`

The `BelongsToArrayColumn` method allows you to define a relationship between a model and an array column on another model. 
if you have ["7", "71"] in array column and int 7 or 71 at your foreign-key then pass `$isString` flag as true to get expected results.

Here's an example:

```php

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
            // optional, default is false (if true then it treats all values as string)
            isString: true 
        );
    }
}

```

With this relationship defined, you can fetch related company founders with the following code:

```php

$company = Company::with('companyFounders')->find(71);

// Founders for company with id 71

$company->companyFounders;

```

This will provide you with data from the `users` table where the `companies` array column contains the value 71.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [MrPunyapal](https://github.com/MrPunyapal)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
