<?php

namespace Spatie\LaravelCiphersweet\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Spatie\LaravelCiphersweet\LaravelCiphersweet
 */
class LaravelCiphersweet extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravel-ciphersweet';
    }
}
