<?php

use Spatie\LaravelCipherSweet\CipherSweetDecryption;
use Spatie\LaravelCipherSweet\Exceptions\RowNotDecrypted;
use Spatie\LaravelCipherSweet\Tests\TestClasses\Note;
use Spatie\LaravelCipherSweet\Tests\TestClasses\Secret;
use Spatie\LaravelCipherSweet\Tests\TestClasses\User;

it('saves a plaintext value that looks like ciphertext', function () {
    $user = User::create([
        'name' => 'John Doe',
        'password' => bcrypt('password'),
        'email' => 'nacl:hello@example.com',
    ]);

    expect(User::find($user->id)->email)->toBe('nacl:hello@example.com');
});

it('can update a stored plaintext value that looks like ciphertext', function () {
    $user = User::create([
        'name' => 'John Doe',
        'password' => bcrypt('password'),
        'email' => 'nacl:hello@example.com',
    ]);

    $reloaded = User::find($user->id);
    $reloaded->update(['name' => 'Changed']);

    expect(User::find($user->id)->email)->toBe('nacl:hello@example.com');
});

it('does not consider a null optional encrypted value encrypted', function () {
    $note = Note::create(['title' => 'Title', 'body' => null, 'meta' => ['secret' => 'shh']]);

    expect($note->isEncryptedInMemory())->toBeFalse()
        ->and(Note::find($note->id)->body)->toBeNull();
});

it('reports an encrypted json field as encrypted', function () {
    $note = Note::create(['title' => 'Title', 'body' => 'Body', 'meta' => ['secret' => 'shh']]);

    $suspended = CipherSweetDecryption::suspend(fn () => Note::find($note->id));

    expect($suspended->isEncryptedInMemory())->toBeTrue();
});

it('refuses to save a suspended model whose json field still holds ciphertext', function () {
    $note = Note::create(['title' => 'Title', 'body' => 'Body', 'meta' => ['secret' => 'shh']]);

    $suspended = CipherSweetDecryption::suspend(fn () => Note::find($note->id));

    expect(fn () => $suspended->save())->toThrow(RowNotDecrypted::class);
});

it('decrypts a suspended json field on demand', function () {
    $note = Note::create(['title' => 'Title', 'body' => 'Body', 'meta' => ['secret' => 'shh']]);

    $suspended = CipherSweetDecryption::suspend(fn () => Note::find($note->id));

    expect($suspended->decryptNow()->title)->toBe('Title')
        ->and($suspended->meta)->toBe(['secret' => 'shh']);
});

it('does not throw from decryptNow when only some encrypted columns were selected', function () {
    $note = Note::create(['title' => 'Title', 'body' => 'Body', 'meta' => ['secret' => 'shh']]);

    $partial = CipherSweetDecryption::suspend(fn () => Note::select('id', 'title')->find($note->id));

    expect(fn () => $partial->decryptNow())->not->toThrow(Exception::class);
});

it('reports a model whose only encrypted field is json as encrypted', function () {
    $secret = Secret::create(['payload' => ['value' => 'shh']]);

    $suspended = CipherSweetDecryption::suspend(fn () => Secret::find($secret->id));

    expect($suspended->isEncryptedInMemory())->toBeTrue();
});

it('refuses to save a suspended model whose only encrypted field is json', function () {
    $secret = Secret::create(['payload' => ['value' => 'shh']]);

    $suspended = CipherSweetDecryption::suspend(fn () => Secret::find($secret->id));

    expect(fn () => $suspended->save())->toThrow(RowNotDecrypted::class);
});

it('keeps a json blind-index-free model readable after saving inside the callback', function () {
    $secret = Secret::create(['payload' => ['value' => 'shh']]);

    CipherSweetDecryption::suspend(fn () => Secret::find($secret->id)->decryptNow()->touch());

    expect(Secret::find($secret->id)->payload)->toBe(['value' => 'shh']);
});
