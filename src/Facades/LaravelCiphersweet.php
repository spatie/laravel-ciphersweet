<?php

namespace Spatie\LaravelCipherSweet\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Spatie\LaravelCipherSweet\LaravelCipherSweet
 */
class LaravelCipherSweet extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravel-ciphersweet';
    }
}
