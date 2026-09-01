<?php

namespace Spatie\LaravelCipherSweet\Tests\TestClasses;

use Illuminate\Database\Eloquent\Model;
use ParagonIE\CipherSweet\EncryptedRow;
use ParagonIE\CipherSweet\JsonFieldMap;
use Spatie\LaravelCipherSweet\Concerns\UsesCipherSweet;
use Spatie\LaravelCipherSweet\Contracts\CipherSweetEncrypted;

class Note extends Model implements CipherSweetEncrypted
{
    use UsesCipherSweet;

    protected $guarded = [];

    public static function configureCipherSweet(EncryptedRow $encryptedRow): void
    {
        $encryptedRow
            ->addTextField('title')
            ->addOptionalTextField('body')
            ->addJsonField('meta', (new JsonFieldMap())->addTextField('secret'));
    }
}
