<?php

use Illuminate\Support\Facades\DB;
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
});
