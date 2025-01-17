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
        $model->saveDirtyAttributesForCipherSweet();

        $model->encryptRow();

        // NOTE: If other listeners are called after this, all the encrypted attributes will appear in the dirty list
        // since each field will contain their encrypted value.
        // Having "Listener priority" might fix this (put the encrypter at the lowest priority
        // so all other listeners are called first), but Laravel doesn't support that (yet).
    }

    public function saved(CipherSweetEncrypted $model): void
    {
        $model->decryptRow();

        $model->excludeNonChangedEncryptedAttributesFromChanges();

        $model->updateBlindIndexes();
    }
}
