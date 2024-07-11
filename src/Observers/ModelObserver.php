<?php

namespace Spatie\LaravelCipherSweet\Observers;

use ParagonIE\CipherSweet\Exception\EmptyFieldException;
use Spatie\LaravelCipherSweet\Contracts\CipherSweetEncrypted;

class ModelObserver
{
    public function deleting(CipherSweetEncrypted $model): void
    {
        $model->deleteBlindIndexes();
    }

    public function retrieved(CipherSweetEncrypted $model): void
    {
        try {
            $model->decryptRow();
        } catch (EmptyFieldException) {
            // Not all fields are available to decrypt.
        }
    }

    public function saving(CipherSweetEncrypted $model): void
    {
        $model->encryptRow();
    }

    public function saved(CipherSweetEncrypted $model): void
    {
        $model->decryptRow();

        $model->updateBlindIndexes();
    }
}
