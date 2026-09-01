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

    expect(fn () => $user->save())->toThrow(RowNotDecrypted::class)
        ->and(storedEmail($this->user->id))->toBe($before);
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

it('keeps blind indexes searchable when a model is created inside the callback', function () {
    CipherSweetDecryption::suspend(fn () => User::create([
        'name' => 'Jane Doe',
        'password' => bcrypt('password'),
        'email' => 'jane@example.com',
    ]));

    expect(User::whereBlind('email', 'email_index', 'jane@example.com')->count())->toBe(1);
});

it('keeps blind indexes searchable when a model is updated inside the callback', function () {
    $user = User::find($this->user->id);

    CipherSweetDecryption::suspend(fn () => $user->update(['name' => 'Changed']));

    expect(User::whereBlind('email', 'email_index', 'john@example.com')->count())->toBe(1);
});

it('decrypts a model again after it was saved inside the callback', function () {
    $user = User::find($this->user->id);

    CipherSweetDecryption::suspend(fn () => $user->update(['name' => 'Changed']));

    expect($user->email)->toBe('john@example.com')
        ->and($user->isEncryptedInMemory())->toBeFalse();
});

it('refuses to save a model that was refreshed inside the callback', function () {
    $user = User::find($this->user->id);
    $before = storedEmail($this->user->id);

    CipherSweetDecryption::suspend(fn () => $user->refresh());

    expect($user->isEncryptedInMemory())->toBeTrue()
        ->and(fn () => $user->save())->toThrow(RowNotDecrypted::class)
        ->and(storedEmail($this->user->id))->toBe($before);
});

it('refuses to save a replica of a model that still holds ciphertext', function () {
    $user = CipherSweetDecryption::suspend(fn () => User::find($this->user->id));

    $replica = $user->replicate();

    expect($replica->isEncryptedInMemory())->toBeTrue()
        ->and(fn () => $replica->save())->toThrow(RowNotDecrypted::class);
});

it('saves a suspended model that was refreshed outside the callback', function () {
    $user = CipherSweetDecryption::suspend(fn () => User::find($this->user->id));

    $user->refresh();

    expect($user->isEncryptedInMemory())->toBeFalse()
        ->and($user->decryptNow()->email)->toBe('john@example.com');

    $user->update(['email' => 'jane@example.com']);

    expect(User::find($this->user->id)->email)->toBe('jane@example.com');
});

it('does not consider a model encrypted when its encrypted columns were not selected', function () {
    $user = CipherSweetDecryption::suspend(fn () => User::select('id', 'name')->find($this->user->id));

    expect($user->isEncryptedInMemory())->toBeFalse()
        ->and($user->decryptNow()->name)->toBe('John Doe');
});

it('suspends decryption for eager loaded relations', function () {
    $employee = User::create([
        'name' => 'Employee',
        'password' => bcrypt('password'),
        'email' => 'employee@example.com',
        'manager_id' => $this->user->id,
    ]);

    $loaded = CipherSweetDecryption::suspend(fn () => User::with('manager')->find($employee->id));

    expect($loaded->manager->email)->toBe(storedEmail($this->user->id))
        ->and($loaded->manager->isEncryptedInMemory())->toBeTrue();
});

function storedEmail(int $id): string
{
    return DB::table('users')->where('id', $id)->value('email');
}
