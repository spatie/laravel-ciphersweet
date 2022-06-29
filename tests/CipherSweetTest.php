<?php

use Illuminate\Support\Facades\DB;
use ParagonIE\CipherSweet\CipherSweet as CipherSweetEngine;
use ParagonIE\CipherSweet\EncryptedRow;
use ParagonIE\ConstantTime\Hex;
use Spatie\LaravelCipherSweet\Tests\TestClasses\User;

beforeEach(function () {
    $this->user = User::create([
        'name' => 'Rias',
        'password' => fake()->password,
        'email' => 'rias@spatie.be',
    ]);
});

it('encrypts and decrypts fields', function () {
    expect(DB::table('users')->first()->email)->toStartWith('nacl:')
        ->and($this->user->email)->toEqual('rias@spatie.be');
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
        'name' => 'Another one',
        'password' => fake()->password,
        'email' => 'foo@bar.com',
    ]);

    expect(User::whereBlind('email', 'email_index', 'rias@spatie.be')->count())->toBe(1);
    expect(User::whereBlind('email', 'email_index', 'rias@spatie.be')->first()->is($this->user))->toBeTrue();
    expect(User::whereBlind('email', 'email_index', 'rias@spatie.be')->first()->is($otherUser))->toBeFalse();

    expect(User::whereBlind('email', 'email_index', 'rias@spatie.be')->orWhereBlind('email', 'email_index', 'foo@bar.com')->count())->toBe(2);
});

it('can rotate keys', function () {
    $originalUser = DB::table('users')->first();

    $this->artisan('ciphersweet:rotate-model-encryption', [
        'model' => User::class,
        'newKey' => $key = Hex::encode(random_bytes(32)),
    ])->assertSuccessful()->expectsOutput('Updated 1 rows.');

    $updatedUser = DB::table('users')->first();

    expect($originalUser?->email)->not()->toBe($updatedUser?->email);

    $this->artisan('ciphersweet:rotate-model-encryption', [
        'model' => User::class,
        'newKey' => $key,
    ])->assertSuccessful()->expectsOutput('Updated 0 rows.');

    try {
        User::first();
    } catch (SodiumException $e) {
        expect($e->getMessage())->toBe('Invalid ciphertext');
    }

    // Reset static instance of CipherSweetEngine
    config()->set('ciphersweet.providers.string.key', $key);
    User::$cipherSweetEncryptedRow = new EncryptedRow(
        app(CipherSweetEngine::class),
        (new User())->getTable()
    );

    User::first(); // Shouldn't throw an exception.
});
