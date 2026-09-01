<?php

namespace Spatie\LaravelCipherSweet\Tests\TestClasses;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use ParagonIE\CipherSweet\BlindIndex;
use ParagonIE\CipherSweet\EncryptedRow;
use Spatie\LaravelCipherSweet\Concerns\UsesCipherSweet;
use Spatie\LaravelCipherSweet\Contracts\CipherSweetEncrypted;

class User extends Model implements CipherSweetEncrypted
{
    use UsesCipherSweet;

    protected $guarded = [];

    public function manager(): BelongsTo
    {
        return $this->belongsTo(self::class, 'manager_id');
    }

    public static function configureCipherSweet(EncryptedRow $encryptedRow): void
    {
        $encryptedRow
            ->addField('email')
            ->addBlindIndex('email', new BlindIndex('email_index'));
    }
}
