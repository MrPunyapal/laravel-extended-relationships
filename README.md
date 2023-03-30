# laravel-extended-relationships

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mr-punyapal/laravel-extended-relationships.svg?style=flat-square)](https://packagist.org/packages/mr-punyapal/laravel-extended-relationships)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/mr-punyapal/laravel-extended-relationships/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/mr-punyapal/laravel-extended-relationships/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/mr-punyapal/laravel-extended-relationships/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/mr-punyapal/laravel-extended-relationships/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/mr-punyapal/laravel-extended-relationships.svg?style=flat-square)](https://packagist.org/packages/mr-punyapal/laravel-extended-relationships)

## Installation

You can install the package via composer:

```bash
composer require mr-punyapal/laravel-extended-relationships
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-extended-relationships-config"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

In Model.

```php

use Mrpunyapal\LaravelExtendedRelationships\LaravelExtendedRelationships;

class Post extends Model{

    use LaravelExtendedRelationships;

    public function auditors(){
        return $this->belongsToManyWithManyKeys(
            User::class,
            'id',
            ['created_by' => 'creator', 'updated_by' => 'updater', 'deleted_by' => 'deleter']
        );
    }
}

```

While fetching data.

```php

$post = Post::with('auditors')->first();

//creator

$post->creator;

//updater

$post->updater;

//deleter

$post->deleter;

```

this is how you can have N number of relationships with defining single relationship.

and single query for all relationship will be fire in database for all the relationships.


```php

use Mrpunyapal\LaravelExtendedRelationships\LaravelExtendedRelationships;

class User extends Model{

    use LaravelExtendedRelationships;

    public function audited(){
        return $this->hasManyWithManyKeys(
            Post::class,
            ['created_by' => 'created', 'updated_by' => 'updated', 'deleted_by' => 'deleted'],
            'id'
        );
    }
}

```

While fetching data.

```php

$post = User::with('audited')->first();

//created

$post->created;

//updated

$post->updated;

//deleted

$post->deleted;

```

if you have column with array of localkeys like [25,60] then you can use.

```php 

use Mrpunyapal\LaravelExtendedRelationships\LaravelExtendedRelationships;

class User extends Model{

    use LaravelExtendedRelationships;

    public function addresses(){
        return $this->hasManyWithColumnKeyArray(
            Address::class,
            'addresses',
            'id'
        );
    }
}

```

While fetching data.

```php

$post = User::with('addresses')->first();

//created

$post->addresses;

```
you will get addresses with ids 25 and 60.

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
