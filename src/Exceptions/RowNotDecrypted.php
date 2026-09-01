<?php

namespace Spatie\LaravelCipherSweet\Exceptions;

use Exception;

class RowNotDecrypted extends Exception
{
    /** @param class-string $modelClass */
    public static function forModel(string $modelClass): self
    {
        return new self("Refusing to save {$modelClass}: its encrypted attributes still hold ciphertext, so encrypting them again would lose the value. Call decryptNow() on the model first.");
    }
}
