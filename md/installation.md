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