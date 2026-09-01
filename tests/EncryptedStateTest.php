<?php

use Illuminate\Support\Facades\DB;
use ParagonIE\CipherSweet\CipherSweet;
use ParagonIE\ConstantTime\Hex;
use Spatie\LaravelCipherSweet\CipherSweetDecryption;
use Spatie\LaravelCipherSweet\Exceptions\RowNotDecrypted;
use Spatie\LaravelCipherSweet\Tests\TestClasses\Note;
use Spatie\LaravelCipherSweet\Tests\TestClasses\Secret;
use Spatie\LaravelCipherSweet\Tests\TestClasses\User;

it('refuses to save a suspended model when one field was reassigned in plaintext', function () {
    $note = createNote();
    $storedBody = storedNoteColumn($note->id, 'body');

    $suspended = CipherSweetDecryption::suspend(fn () => Note::find($note->id));
    $suspended->title = 'A new title';

    expect(fn () => $suspended->save())->toThrow(RowNotDecrypted::class)
        ->and(storedNoteColumn($note->id, 'body'))->toBe($storedBody);
});

it('refuses to save a suspended model whose encrypted columns were not all selected', function () {
    $note = createNote();
    $storedBody = storedNoteColumn($note->id, 'body');

    $partial = CipherSweetDecryption::suspend(fn () => Note::select('id', 'title')->find($note->id));

    expect(fn () => $partial->save())->toThrow(RowNotDecrypted::class)
        ->and(storedNoteColumn($note->id, 'body'))->toBe($storedBody);
});

it('refuses to save a suspended model the current key cannot decrypt', function () {
    $user = User::create([
        'name' => 'John Doe',
        'password' => bcrypt('password'),
        'email' => 'john@example.com',
    ]);
    $storedEmail = DB::table('users')->where('id', $user->id)->value('email');

    $suspended = CipherSweetDecryption::suspend(fn () => User::find($user->id));

    rotateCipherSweetKey();

    expect($suspended->isEncryptedInMemory())->toBeTrue()
        ->and(fn () => $suspended->save())->toThrow(RowNotDecrypted::class)
        ->and(DB::table('users')->where('id', $user->id)->value('email'))->toBe($storedEmail);
});

it('saves a json field whose mapped leaf is null', function () {
    $secret = Secret::create(['payload' => ['value' => null]]);

    expect($secret->exists)->toBeTrue();
});

it('saves a plaintext field holding a value copied from a ciphertext column', function () {
    $user = User::create([
        'name' => 'John Doe',
        'password' => bcrypt('password'),
        'email' => 'john@example.com',
    ]);
    $ciphertext = DB::table('users')->where('id', $user->id)->value('email');

    $pasted = User::create([
        'name' => 'Jane Doe',
        'password' => bcrypt('password'),
        'email' => $ciphertext,
    ]);

    expect($pasted->exists)->toBeTrue();
});

it('saves a new model that was refreshed inside the callback', function () {
    $user = new User([
        'name' => 'John Doe',
        'password' => bcrypt('password'),
        'email' => 'john@example.com',
    ]);

    CipherSweetDecryption::suspend(fn () => $user->refresh());

    $user->save();

    expect(User::find($user->id)->email)->toBe('john@example.com');
});

function createNote(): Note
{
    return Note::create(['title' => 'Title', 'body' => 'Body', 'meta' => ['secret' => 'shh']]);
}

function storedNoteColumn(int $id, string $column): ?string
{
    return DB::table('notes')->where('id', $id)->value($column);
}

function rotateCipherSweetKey(): void
{
    config()->set('ciphersweet.providers.string.key', Hex::encode(random_bytes(32)));

    app()->forgetInstance(CipherSweet::class);

    User::$cipherSweetEncryptedRow = null;
    Note::$cipherSweetEncryptedRow = null;
    Secret::$cipherSweetEncryptedRow = null;
}
