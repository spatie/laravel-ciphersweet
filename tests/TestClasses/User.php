<?php

namespace Spatie\LaravelCipherSweet\Tests\TestClasses;

use Illuminate\Database\Eloquent\Model;
use ParagonIE\CipherSweet\BlindIndex;
use ParagonIE\CipherSweet\EncryptedRow;
use Spatie\LaravelCipherSweet\UsesCipherSweet;

class User extends Model
{
    use UsesCipherSweet;

    protected $guarded = [];

    protected static function configureCipherSweet(EncryptedRow $encryptedRow): void
    {
        $encryptedRow->addField('email')
            ->addBlindIndex('email', new BlindIndex('email_index'));
    }
}
