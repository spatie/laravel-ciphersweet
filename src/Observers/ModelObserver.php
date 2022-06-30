<?php

namespace Spatie\LaravelCipherSweet\Observers;

use ErrorException;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelCipherSweet\Contracts\CipherSweetEncrypted;

class ModelObserver
{
    public function deleting(CipherSweetEncrypted $model)
    {
        DB::table('blind_indexes')
            ->where('indexable_type', $model->getMorphClass())
            ->where('indexable_id', $model->getKey())
            ->delete();
    }

    public function retrieved(CipherSweetEncrypted $model)
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
