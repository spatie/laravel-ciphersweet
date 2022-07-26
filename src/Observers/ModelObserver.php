<?php

namespace Spatie\LaravelCipherSweet\Observers;

use ErrorException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
        } catch (ErrorException) {
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
