<?php

namespace Spatie\LaravelCipherSweet\Contracts;

use Illuminate\Contracts\Database\Query\Builder;
use ParagonIE\CipherSweet\EncryptedRow;

/** @mixin \Illuminate\Database\Eloquent\Model */
interface CipherSweetEncrypted
{
    public static function configureCipherSweet(EncryptedRow $encryptedRow): void;

    public function encryptRow(): void;

    public function decryptRow(): void;

    public function updateBlindIndexes(): void;

    public function deleteBlindIndexes(): void;

    public function scopeWhereBlind(
        Builder $query,
        string $column,
        string $indexName,
        string|array $value
    ): Builder;

    public function scopeOrWhereBlind(
        Builder $query,
        string $column,
        string $indexName,
        string|array $value
    ): Builder;
}
