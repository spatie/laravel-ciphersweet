<?php

namespace Spatie\LaravelCipherSweet\Exceptions;

use Exception;

class RowNotDecrypted extends Exception
{
    public static function make(string $model): self
    {
        return new self(
            "Refusing to save {$model}: it was retrieved while decryption was suspended, so saving "
            . 'it would encrypt the stored ciphertext a second time and lose the value. Call '
            . 'decryptNow() on the model first.',
        );
    }
}
