<?php

use Illuminate\Support\Facades\DB;
use ParagonIE\CipherSweet\CipherSweet as CipherSweetEngine;
use ParagonIE\CipherSweet\EncryptedRow;
use ParagonIE\ConstantTime\Hex;

use function Pest\Laravel\artisan;

use Spatie\LaravelCipherSweet\Commands\EncryptCommand;
use Spatie\LaravelCipherSweet\Commands\GenerateKeyCommand;
use Spatie\LaravelCipherSweet\Tests\TestClasses\User;

beforeEach(function () {
    $this->user = User::create([
        'name' => 'John Doe',
        'password' => bcrypt('password'),
        'email' => 'john@example.com',
    ]);
});

it('can generate a key', function () {
    artisan(GenerateKeyCommand::class)->assertSuccessful();
});

it('encrypts and decrypts fields', function () {
    expect(DB::table('users')->first()->email)->toStartWith('nacl:')
        ->and($this->user->email)->toEqual('john@example.com');
});

it('can create blind indexes', function () {
    expect(DB::table('blind_indexes')->count())->toBe(1);
});

it('can cleans up blind indexes', function () {
    expect(DB::table('blind_indexes')->count())->toBe(1);

    $this->user->delete();

    expect(DB::table('blind_indexes')->count())->toBe(0);
});

it('can scope on blind indexes', function () {
    $otherUser = User::create([
        'name' => 'Jane Doe',
        'password' => fake()->password,
        'email' => 'jane@another.com',
    ]);

    expect(User::whereBlind('email', 'email_index', 'john@example.com')->count())->toBe(1);
    expect(User::whereBlind('email', 'email_index', 'john@example.com')->first()->is($this->user))->toBeTrue();
    expect(User::whereBlind('email', 'email_index', 'john@example.com')->first()->is($otherUser))->toBeFalse();

    expect(User::whereBlind('email', 'email_index', 'john@example.com')->orWhereBlind('email', 'email_index', 'jane@another.com')->count())->toBe(2);
});

it('can rotate keys', function () {
    $originalUser = DB::table('users')->first();

    $this
        ->artisan(EncryptCommand::class, [
        'model' => User::class,
        'newKey' => $key = Hex::encode(random_bytes(32)),
    ])
        ->assertSuccessful()
        ->expectsOutput('Updated 1 rows.');

    $updatedUser = DB::table('users')->first();

    expect($originalUser?->email)->not()->toBe($updatedUser?->email);

    $this
        ->artisan(EncryptCommand::class, [
        'model' => User::class,
        'newKey' => $key,
    ])
        ->assertSuccessful()
        ->expectsOutput('Updated 0 rows.');

    try {
        User::first();
    } catch (SodiumException $exception) {
        expect($exception->getMessage())->toBe('Invalid ciphertext');
    }

    resetCipherSweet($key);

    User::first(); // Shouldn't throw an exception.
});

it('can encrypt rows when they werent encrypted', function () {
    DB::table('users')->update([
        'email' => 'john@example.com',
    ]);

    $originalUser = DB::table('users')->first();

    artisan(EncryptCommand::class, [
        'model' => User::class,
        'newKey' => $key = Hex::encode(random_bytes(32)),
    ])->assertSuccessful()->expectsOutput('Updated 1 rows.');

    $updatedUser = DB::table('users')->first();

    expect($originalUser?->email)->not()->toBe($updatedUser?->email);

    artisan(EncryptCommand::class, [
        'model' => User::class,
        'newKey' => $key,
    ])->assertSuccessful()->expectsOutput('Updated 0 rows.');

    try {
        User::first();
    } catch (SodiumException $e) {
        expect($e->getMessage())->toBe('Invalid ciphertext');
    }

    resetCipherSweet($key);

    User::first(); // Shouldn't throw an exception.
});

function resetCipherSweet($key)
{
    config()->set('ciphersweet.providers.string.key', $key);
    User::$cipherSweetEncryptedRow = new EncryptedRow(
        app(CipherSweetEngine::class),
        (new User())->getTable()
    );
}
