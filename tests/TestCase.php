<?php

namespace Spatie\LaravelCipherSweet\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use ParagonIE\ConstantTime\Hex;
use Spatie\LaravelCipherSweet\LaravelCipherSweetServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Spatie\\LaravelCipherSweet\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

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

        $migration = include __DIR__.'/../vendor/orchestra/testbench-core/laravel/migrations/2014_10_12_000000_testbench_create_users_table.php';
        $migration->up();
        $migration = include __DIR__.'/../database/migrations/create_blind_indexes_table.php.stub';
        $migration->up();
    }
}
