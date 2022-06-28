<?php

namespace Spatie\LaravelCipherSweet;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use ParagonIE\CipherSweet\CipherSweet as CipherSweetEngine;
use ParagonIE\CipherSweet\EncryptedRow;
use ParagonIE\CipherSweet\KeyProvider\StringProvider;
use ParagonIE\CipherSweet\KeyRotation\RowRotator;

class RotateModelEncryptionCommand extends Command
{
    protected $signature = 'ciphersweet:rotate-model-encryption {model} {newKey}';

    protected $description = 'Rotate the encryption keys for a model';

    public function handle(): int
    {
        $this->info('Rotating encryption keys for all models');

        /** @var class-string<\Spatie\LaravelCipherSweet\UsesCipherSweet> $modelClass */
        $modelClass = $this->argument('model');

        if (! class_exists($modelClass)) {
            $this->error("Model {$modelClass} does not exist");

            return self::INVALID;
        }

        $newClass = (new $modelClass());
        $updatedRows = 0;
        DB::table($newClass->getTable())->orderBy((new $modelClass())->getKeyName())->each(function (object $model) use ($modelClass, $newClass, &$updatedRows) {
            $model = (array) $model;

            /** @var EncryptedRow $oldRow */
            $oldRow = $modelClass::getCipherSweetConfig();
            $newRow = new EncryptedRow(
                new CipherSweetEngine(new StringProvider($this->argument('newKey'))),
                $newClass->getTable(),
            );
            $modelClass::configureCipherSweet($newRow);

            $rotator = new RowRotator($oldRow, $newRow);
            if ($rotator->needsReEncrypt($model)) {
                [$ciphertext, $indices] = $rotator->prepareForUpdate($model);

                DB::table($newClass->getTable())
                    ->where($newClass->getKeyName(), $model[$newClass->getKeyName()])
                    ->update(Arr::only($ciphertext, $newRow->listEncryptedFields()));

                foreach ($indices as $name => $value) {
                    DB::table('blind_indexes')->updateOrInsert([
                        'indexable_type' => $newClass->getMorphClass(),
                        'indexable_id' => $model[$newClass->getKeyName()],
                        'name' => $name,
                    ], [
                        'value' => $value,
                    ]);
                }

                $updatedRows++;
            }
        });

        $this->info("Updated {$updatedRows} rows.");
        $this->info("You can now set your config key to the new key.");

        return self::SUCCESS;
    }
}
