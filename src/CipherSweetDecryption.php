<?php

namespace Spatie\LaravelCipherSweet;

use Closure;

class CipherSweetDecryption
{
    protected static bool $suspended = false;

    /**
     * Run the callback without decrypting models as they are retrieved.
     *
     * @template TReturn
     *
     * @param  Closure(): TReturn  $callback
     * @return TReturn
     */
    public static function suspend(Closure $callback): mixed
    {
        $previous = static::$suspended;
        static::$suspended = true;

        try {
            return $callback();
        } finally {
            static::$suspended = $previous;
        }
    }

    public static function isSuspended(): bool
    {
        return static::$suspended;
    }
}
