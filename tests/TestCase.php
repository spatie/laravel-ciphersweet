<?php

namespace Spatie\LaravelCipherSweet\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use ParagonIE\ConstantTime\Hex;
use Spatie\LaravelCipherSweet\LaravelCipherSweetServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            LaravelCipherSweetServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        config()->set('ciphersweet.providers.string.key', Hex::encode(random_bytes(32)));

        if (file_exists(__DIR__.'/../vendor/orchestra/testbench-core/laravel/migrations/2014_10_12_000000_testbench_create_users_table.php')) {
            $migration = include __DIR__.'/../vendor/orchestra/testbench-core/laravel/migrations/2014_10_12_000000_testbench_create_users_table.php';
            $migration->up();
        }

        if (file_exists(__DIR__.'/../vendor/orchestra/testbench-core/laravel/migrations/0001_01_01_000000_testbench_create_users_table.php')) {
            $migration = include __DIR__.'/../vendor/orchestra/testbench-core/laravel/migrations/0001_01_01_000000_testbench_create_users_table.php';
            $migration->up();
        }

        $migration = include __DIR__.'/../database/migrations/create_blind_indexes_table.php';
        $migration->up();
    }
}
