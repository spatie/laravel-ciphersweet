<?php

use Illuminate\Support\Facades\DB;
use Spatie\LaravelCipherSweet\CipherSweetDecryption;
use Spatie\LaravelCipherSweet\Exceptions\RowNotDecrypted;
use Spatie\LaravelCipherSweet\Tests\TestClasses\User;

beforeEach(function () {
    $this->user = User::create([
        'name' => 'John Doe',
        'password' => bcrypt('password'),
        'email' => 'john@example.com',
    ]);
});

function storedEmail(int $id): string
{
    return DB::table('users')->where('id', $id)->value('email');
}

it('does not decrypt while decryption is suspended', function () {
    $user = CipherSweetDecryption::suspend(fn () => User::find($this->user->id));

    expect($user->email)->toBe(storedEmail($this->user->id))
        ->and($user->email)->toStartWith('nacl:');
});

it('reports that a suspended model is still encrypted', function () {
    $user = CipherSweetDecryption::suspend(fn () => User::find($this->user->id));

    expect($user->isEncryptedInMemory())->toBeTrue();
});

it('decrypts again once the callback has returned', function () {
    CipherSweetDecryption::suspend(fn () => User::find($this->user->id));

    expect(User::find($this->user->id)->email)->toBe('john@example.com');
});

it('can decrypt a suspended model on demand', function () {
    $user = CipherSweetDecryption::suspend(fn () => User::find($this->user->id));

    expect($user->decryptNow()->email)->toBe('john@example.com')
        ->and($user->isEncryptedInMemory())->toBeFalse();
});

it('does not decrypt an already decrypted model again', function () {
    $user = CipherSweetDecryption::suspend(fn () => User::find($this->user->id));
    $user->decryptNow();

    expect($user->decryptNow()->email)->toBe('john@example.com');
});

it('can decrypt on demand from inside the suspending callback', function () {
    $email = CipherSweetDecryption::suspend(fn () => User::find($this->user->id)->decryptNow()->email);

    expect($email)->toBe('john@example.com');
});

it('refuses to save a model that was retrieved without decryption', function () {
    $user = CipherSweetDecryption::suspend(fn () => User::find($this->user->id));

    expect(fn () => $user->save())->toThrow(RowNotDecrypted::class);
});

it('leaves the stored ciphertext untouched when refusing to save', function () {
    $before = storedEmail($this->user->id);
    $user = CipherSweetDecryption::suspend(fn () => User::find($this->user->id));

    rescue(fn () => $user->save());

    expect(storedEmail($this->user->id))->toBe($before);
});

it('saves normally once a suspended model has been decrypted', function () {
    $user = CipherSweetDecryption::suspend(fn () => User::find($this->user->id));

    $user->decryptNow()->update(['email' => 'jane@example.com']);

    expect(User::find($this->user->id)->email)->toBe('jane@example.com');
});

it('restores the previous state when the callback throws', function () {
    rescue(fn () => CipherSweetDecryption::suspend(function () {
        throw new RuntimeException('the callback failed');
    }));

    expect(CipherSweetDecryption::isSuspended())->toBeFalse()
        ->and(User::find($this->user->id)->email)->toBe('john@example.com');
});

it('keeps decryption suspended for the rest of an outer callback', function () {
    $email = CipherSweetDecryption::suspend(function () {
        CipherSweetDecryption::suspend(fn () => User::find($this->user->id));

        return User::find($this->user->id)->email;
    });

    expect($email)->toStartWith('nacl:');
});
