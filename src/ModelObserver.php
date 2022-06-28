<?php

namespace Spatie\LaravelCipherSweet;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ModelObserver
{
    /**
     * @param Model|UsesCipherSweet $model
     * @throws \SodiumException
     */
    public function deleting(Model $model)
    {
        DB::table('blind_indexes')
            ->where('indexable_type', $model->getMorphClass())
            ->where('indexable_id', $model->getKey())
            ->delete();
    }

    /**
     * @param Model|UsesCipherSweet $model
     * @throws \ParagonIE\CipherSweet\Exception\CryptoOperationException
     * @throws \SodiumException
     */
    public function retrieved(Model $model)
    {
        $model->decryptRow();
    }

    /**
     * @param Model|UsesCipherSweet $model
     * @throws \ParagonIE\CipherSweet\Exception\ArrayKeyException
     * @throws \ParagonIE\CipherSweet\Exception\CryptoOperationException
     * @throws \SodiumException
     */
    public function saving(Model $model)
    {
        $model->encryptRow();
    }

    /**
     * @param Model|UsesCipherSweet $model
     * @throws \ParagonIE\CipherSweet\Exception\ArrayKeyException
     * @throws \ParagonIE\CipherSweet\Exception\CryptoOperationException
     * @throws \SodiumException
     */
    public function saved(Model $model)
    {
        $model->decryptRow();
        $model->updateBlindIndexes();
    }
}
