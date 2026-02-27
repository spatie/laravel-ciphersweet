<?php

namespace Spatie\LaravelCipherSweet\Concerns;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use ParagonIE\CipherSweet\CipherSweet as CipherSweetEngine;
use ParagonIE\CipherSweet\EncryptedRow;
use Spatie\LaravelCipherSweet\Observers\ModelObserver;

/** @mixin \Illuminate\Database\Eloquent\Model */
trait UsesCipherSweet
{
    public static ?EncryptedRow $cipherSweetEncryptedRow = null;

    // Keeps which attributes were really dirty when saving
    protected array $cipherSweetSavingUnencryptedAttributes = [];

    protected static function bootUsesCipherSweet()
    {
        static::$cipherSweetEncryptedRow = null;

        static::retrieved(function ($model) {
            app(ModelObserver::class)->retrieved($model);
        });
        static::saving(function ($model) {
            app(ModelObserver::class)->saving($model);
        });
        static::saved(function ($model) {
            app(ModelObserver::class)->saved($model);
        });
        static::deleting(function ($model) {
            app(ModelObserver::class)->deleting($model);
        });
    }

    public static function getCipherSweetEncryptedRow(): EncryptedRow
    {
        if (static::$cipherSweetEncryptedRow === null) {
            $model = (new \ReflectionClass(static::class))->newInstanceWithoutConstructor();

            static::$cipherSweetEncryptedRow = new EncryptedRow(
                app(CipherSweetEngine::class),
                $model->getTable()
            );

            static::configureCipherSweet(static::$cipherSweetEncryptedRow);
        }

        return static::$cipherSweetEncryptedRow;
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
        $fieldsToEncrypt = static::getCipherSweetEncryptedRow()->listEncryptedFields();

        $attributes = $this->getAttributes();

        foreach ($fieldsToEncrypt as $field) {
            $attributes[$field] ??= null;
        }

        $this->setRawAttributes(static::getCipherSweetEncryptedRow()->encryptRow($attributes));
    }

    public function updateBlindIndexes(): void
    {
        foreach (static::getCipherSweetEncryptedRow()->getAllBlindIndexes($this->getAttributes()) as $name => $blindIndex) {
            DB::table('blind_indexes')->upsert([
                'value' => $blindIndex,
                'indexable_type' => $this->getMorphClass(),
                'indexable_id' => $this->getKey(),
                'name' => $name,
            ], [
                'indexable_type',
                'indexable_id',
                'name',
            ]);
        }
    }

    public function deleteBlindIndexes(): void
    {
        DB::table('blind_indexes')
            ->where('indexable_type', $this->getMorphClass())
            ->where('indexable_id', $this->getKey())
            ->delete();
    }

    public function decryptRow(): void
    {
        $this->setRawAttributes(static::getCipherSweetEncryptedRow()->setPermitEmpty(config('ciphersweet.permit_empty', false))
            ->decryptRow($this->getAttributes()), true);
    }

    public function scopeWhereBlind(
        Builder $query,
        string $column,
        string $indexName,
        string|array $value
    ): Builder {
        return $query->whereExists(fn (Builder $query): Builder => $this->buildBlindQuery($query, $column, $indexName, $value));
    }

    public function scopeOrWhereBlind(
        Builder $query,
        string $column,
        string $indexName,
        string|array $value
    ): Builder {
        return $query->orWhereExists(fn (Builder $query): Builder => $this->buildBlindQuery($query, $column, $indexName, $value));
    }

    public function excludeNonChangedEncryptedAttributesFromChanges(): self
    {
        // Changes will contain the encrypted fields, event when none of these fields were changed
        // (because Laravel will compare the unencrypted value with the encrypted one which will never match)
        $changes = $this->getChanges();

        // Remove all encrypted attributes that were not previously dirty
        if (! empty($changes)) {
            foreach (static::getCipherSweetEncryptedRow()->listEncryptedFields() as $field) {
                if (! array_key_exists($field, $this->cipherSweetSavingUnencryptedAttributes)) {
                    unset($changes[$field]);
                } else {
                    // Use unencrypted value instead of encrypted
                    $changes[$field] = $this->cipherSweetSavingUnencryptedAttributes[$field];
                }
            }

            $this->changes = $changes;
        }

        $this->cipherSweetSavingUnencryptedAttributes = [];

        return $this;
    }

    public function saveDirtyAttributesForCipherSweet(): self
    {
        $this->cipherSweetSavingUnencryptedAttributes = $this->getDirty();

        return $this;
    }

    private function buildBlindQuery(
        Builder $query,
        string $column,
        string $indexName,
        string|array $value
    ): Builder {
        return $query->select(DB::raw(1))
            ->from('blind_indexes')
            ->where('indexable_type', $this->getMorphClass())
            ->where('indexable_id', DB::raw(DB::getTablePrefix().$this->getTable() . '.' . $this->getKeyName()))
            ->where('name', $indexName)
            ->where('value', static::getCipherSweetEncryptedRow()->getBlindIndex($indexName, [$column => $value]));
    }
}
