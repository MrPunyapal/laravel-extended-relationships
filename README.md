# laravel-extended-relationships

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mr-punyapal/laravel-extended-relationships.svg?style=flat-square)](https://packagist.org/packages/mr-punyapal/laravel-extended-relationships)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/mr-punyapal/laravel-extended-relationships/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/mr-punyapal/laravel-extended-relationships/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/mr-punyapal/laravel-extended-relationships/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/mr-punyapal/laravel-extended-relationships/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
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

First, include the `LaravelExtendedRelationships` trait in your model:

```php

use Mrpunyapal\LaravelExtendedRelationships\LaravelExtendedRelationships;

class Post extends Model {
    use LaravelExtendedRelationships;

    //...
}

```

Next, define the `BelongsToManyWithManyKeys` relationship with the `belongsToManyWithManyKeys` method:

```php

public function auditors() {
    return $this->belongsToManyWithManyKeys(
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
* An array mapping the related table's foreign key names to the corresponding attribute names on the model (`['created_by' => 'creator', ...]`)

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

use Mrpunyapal\LaravelExtendedRelationships\LaravelExtendedRelationships;

class User extends Model{

    use LaravelExtendedRelationships;

    public function audited(){
        return $this->hasManyWithManyKeys(
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

### Bonus Relationship

If you have a column posts in your users table which stores an array of local keys like [25, 60], you can use the following relationship:

```php 

use Mrpunyapal\LaravelExtendedRelationships\LaravelExtendedRelationships;

class User extends Model
{
    use LaravelExtendedRelationships;

    public function posts()
    {
        return $this->hasManyWithColumnKeyArray(Post::class, 'posts', 'id');
    }
}

```

When fetching data, you can retrieve the related posts with:

```php

$user = User::with('posts')->first();

// get posts with ids 25 and 60
$user->posts;

```
This allows you to easily retrieve related records with an array of local keys, which can be useful in certain scenarios.

## Note:

Right now, the `belongsToManyWithManyKeys` and `hasManyWithManyKeys` methods work well with eager loading of the relation. However, when loading relations of a single model, the data may not be sorted as expected (e.g., in the order of "updater", "creator", etc.). Instead, all data will be returned as auditors. This functionality will be added in future updates.

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
