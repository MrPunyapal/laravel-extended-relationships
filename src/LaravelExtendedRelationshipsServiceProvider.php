<?php

namespace Mrpunyapal\LaravelExtendedRelationships;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Mrpunyapal\LaravelExtendedRelationships\Commands\LaravelExtendedRelationshipsCommand;

class LaravelExtendedRelationshipsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-extended-relationships')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel-extended-relationships_table')
            ->hasCommand(LaravelExtendedRelationshipsCommand::class);
    }
}
