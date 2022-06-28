<?php

namespace Spatie\LaravelCipherSweet;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use ParagonIE\CipherSweet\CipherSweet as CipherSweetEngine;
use ParagonIE\CipherSweet\EncryptedRow;

/** @mixin \Illuminate\Database\Eloquent\Model */
trait UsesCipherSweet
{
    public static EncryptedRow $cipherSweetEncryptedRow;

    public static function getCipherSweetConfig(): EncryptedRow
    {
        return static::$cipherSweetEncryptedRow;
    }

    protected static function bootUsesCipherSweet()
    {
        static::observe(ModelObserver::class);

        static::$cipherSweetEncryptedRow = new EncryptedRow(
            app(CipherSweetEngine::class),
            (new static())->getTable()
        );

        static::configureCipherSweet(static::$cipherSweetEncryptedRow);
    }

    abstract public static function configureCipherSweet(EncryptedRow $encryptedRow): void;

    /**
     * @return void
     * @throws \ParagonIE\CipherSweet\Exception\ArrayKeyException
     * @throws \ParagonIE\CipherSweet\Exception\CryptoOperationException
     * @throws \SodiumException
     */
    public function encryptRow(): void
    {
        $fieldsToEncrypt = static::$cipherSweetEncryptedRow->listEncryptedFields();
        $attributes = $this->getAttributes();

        foreach ($fieldsToEncrypt as $field) {
            $attributes[$field] ??= '';
        }

        $this->setRawAttributes(static::$cipherSweetEncryptedRow->encryptRow($attributes));
    }

    public function updateBlindIndexes(): void
    {
        foreach (static::$cipherSweetEncryptedRow->getAllBlindIndexes($this->getAttributes()) as $name => $blindIndex) {
            DB::table('blind_indexes')->updateOrInsert([
                'indexable_type' => $this->getMorphClass(),
                'indexable_id' => $this->getKey(),
                'name' => $name,
            ], [
                'value' => $blindIndex,
            ]);
        }
    }

    public function decryptRow(): void
    {
        $this->setRawAttributes(static::$cipherSweetEncryptedRow->decryptRow($this->getAttributes()), true);
    }

    public function scopeWhereBlind(Builder $query, string $column, string $indexName, string|array $value): Builder
    {
        $joins = collect($query->getQuery()->joins)->filter(fn (JoinClause $join) => $join->table === 'blind_indexes');
        $query
            ->select($this->getTable().'.*')
            ->when($joins->count() === 0, function (Builder $query) {
                $query->join('blind_indexes', function (Builder $query) {
                    $query->where('indexable_type', $this->getMorphClass());
                    $query->where('indexable_id', DB::raw($this->getTable().'.'.$this->getKeyName()));
                });
            })
            ->where('blind_indexes.name', $indexName)
            ->where('blind_indexes.value', static::$cipherSweetEncryptedRow->getBlindIndex($indexName, [$column => $value]));

        return $query;
    }

    public function scopeOrWhereBlind(Builder $query, string $column, string $indexName, string|array $value): Builder
    {
        $joins = collect($query->getQuery()->joins)->filter(fn (JoinClause $join) => $join->table === 'blind_indexes');
        $query
            ->select($this->getTable().'.*')
            ->when($joins->count() === 0, function (Builder $query) {
                $query->join('blind_indexes', function (Builder $query) {
                    $query->where('indexable_type', $this->getMorphClass());
                    $query->where('indexable_id', DB::raw($this->getTable().'.'.$this->getKeyName()));
                });
            })
            ->orWhere(function (Builder $query) use ($value, $column, $indexName) {
                $query
                    ->where('blind_indexes.name', $indexName)
                    ->where('blind_indexes.value', static::$cipherSweetEncryptedRow->getBlindIndex($indexName, [$column => $value]));
            });

        return $query;
    }
}
