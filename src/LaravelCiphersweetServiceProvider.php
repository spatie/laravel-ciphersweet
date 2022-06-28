<?php

namespace Spatie\LaravelCipherSweet;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\LaravelCipherSweet\Commands\LaravelCipherSweetCommand;

class LaravelCipherSweetServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-ciphersweet')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel-ciphersweet_table')
            ->hasCommand(LaravelCipherSweetCommand::class);
    }
}
