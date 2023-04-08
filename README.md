# laravel-extended-relationships

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mr-punyapal/laravel-extended-relationships.svg?style=flat-square)](https://packagist.org/packages/mr-punyapal/laravel-extended-relationships)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/mr-punyapal/laravel-extended-relationships/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/mr-punyapal/laravel-extended-relationships/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/mr-punyapal/laravel-extended-relationships.svg?style=flat-square)](https://packagist.org/packages/mr-punyapal/laravel-extended-relationships)

### What is a need of extended relationships?
The laravel-extended-relationships package provides additional, more efficient relationship methods for Laravel Eloquent models. The package offers several useful features such as reducing the number of database queries, improving performance, and minimizing duplicate code.
  
I faced issue and made my own relationships then realise if I can use packages from open source then I can make one too and made this package.

## Installation

You can install the package via composer:

```bash
composer require mr-punyapal/laravel-extended-relationships
```

## Usage

First, include the `HasExtendedRelationships` trait in your model:

```php

use Mrpunyapal\LaravelExtendedRelationships\HasExtendedRelationships;

class Post extends Model {
    use HasExtendedRelationships;

    //...
}

```

Next, define the `BelongsToManyKeys` relationship with the `belongsToManyKeys` method:

```php

public function auditors() {
    return $this->belongsToManyKeys(
        User::class,
        'id',
        [
            'created_by' => 'creator',
            'updated_by' => 'updater',
            'deleted_by' => 'deleter'
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
$post->creator;

// Get the updater
$post->updater;

// Get the deleter
$post->deleter;


```

This allows you to define multiple relationships with just one method, and only a single query is fired in the database for all the relationships.



### Inverse relationship.


```php

use Mrpunyapal\LaravelExtendedRelationships\HasExtendedRelationships;

class User extends Model{

    use HasExtendedRelationships;

    public function audited(){
        return $this->hasManyKeys(
            Post::class,
            [
                'created_by' => 'created', 
                'updated_by' => 'updated', 
                'deleted_by' => 'deleted'
            ],
            'id'
        );
    }
}

```

To retrieve the audited posts of a user, you can use the audited relationship. Here's an example:

```php

$user = User::with('audited')->first();

// Get posts created by the user
$user->created;

// Get posts updated by the user
$user->updated;

// Get posts deleted by the user
$user->deleted;

```

This allows you to define multiple relationships between models with a single method call, simplifying your code and reducing the number of queries executed.

### HasManyArrayColumn

If you have a column companies in your users table which stores an array of local keys like [7, 71], you can use the following relationship:

```php 

use Mrpunyapal\LaravelExtendedRelationships\HasExtendedRelationships;

class User extends Model
{
    use HasExtendedRelationships;

    protected $casts=[
       'companies' => 'array'
    ];

    public function myCompanies()
    {
        return $this->hasManyArrayColumn(
             Company::class,
             'id',
             'companies'
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

The `BelongsToArrayColumn` method allows you to define a relationship between a model and an array column on another model. Here's an example:

```php

use Mrpunyapal\LaravelExtendedRelationships\HasExtendedRelationships;

class Company extends Model
{
    use HasExtendedRelationships;

    public function companyFounders()
    {
        return $this->belongsToArrayColumn(
            User::class,
            'id',
            'companies'
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

## Note:

Right now, the `BelongsToManyKeys` and `HasManyKeys` methods work well with eager loading of the relation. However, when loading relations of a single model, the data may not be sorted as expected (e.g., in the order of "updater", "creator", etc.). Instead, all data will be returned as auditors. This functionality will be added in future updates.

While `BelongsToArrayColumn` may work well with data such as `[7,71]`, it may not function properly if the data in the database is `["7","71"]`. It is possible that this issue will be addressed in future updates.

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

- [mr-punyapal](https://github.com/mr-punyapal)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
