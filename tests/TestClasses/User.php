<?php

namespace Spatie\LaravelCipherSweet\Tests\TestClasses;

use Illuminate\Database\Eloquent\Model;
use ParagonIE\CipherSweet\BlindIndex;
use ParagonIE\CipherSweet\EncryptedRow;
use Spatie\LaravelCipherSweet\Contracts\CipherSweetEncrypted;
use Spatie\LaravelCipherSweet\Concerns\UsesCipherSweet;

class User extends Model implements CipherSweetEncrypted
{
    use UsesCipherSweet;

    protected $guarded = [];

    public static function configureCipherSweet(EncryptedRow $encryptedRow): void
    {
        $encryptedRow->addField('email')
            ->addBlindIndex('email', new BlindIndex('email_index'));
    }
}
