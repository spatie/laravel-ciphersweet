<?php

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\LaravelCipherSweet\Contracts\CipherSweetEncrypted;
use Spatie\LaravelCipherSweet\Rules\EncryptedUniqueRule;
use Spatie\LaravelCipherSweet\Tests\TestClasses\NormalUser;
use Spatie\LaravelCipherSweet\Tests\TestClasses\User;

// Setup a fake model that implements CipherSweetEncrypted
beforeEach(function () {
    User::truncate();
});

it('passes when value is unique', function () {
    $rule = new EncryptedUniqueRule(User::class, 'email_index');

    $validator = Validator::make([
        'email' => 'unique@example.com',
    ], [
        'email' => [$rule],
    ]);

    expect($validator->passes())->toBeTrue();
});

it('fails when value already exists', function () {
    User::create([
        'name' => 'John Doe',
        'password' => bcrypt('password'),
        'email' => 'duplicate@example.com',
    ]);

    $rule = new EncryptedUniqueRule(User::class, 'email_index');

    $validator = Validator::make([
        'email' => 'duplicate@example.com',
    ], [
        'email' => [$rule],
    ]);

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->first('email'))->toContain('The email has already been taken');
});

it('ignores the given id when using ignore method', function () {
    $user = User::create([
        'name' => 'John Doe',
        'password' => bcrypt('password'),
        'email' => 'someone@example.com',
    ]);

    $rule = (new EncryptedUniqueRule(User::class, 'email_index'))->ignore($user->id);

    $validator = Validator::make([
        'email' => 'someone@example.com',
    ], [
        'email' => [$rule],
    ]);

    expect($validator->passes())->toBeTrue();
});

it('throws exception if model does not implement CipherSweetEncrypted', function () {
    $rule = new EncryptedUniqueRule(NormalUser::class, 'email_index');

    Validator::make([
        'email' => 'example@example.com',
    ], [
        'email' => [$rule],
    ])->passes(); // This should throw
})->throws(RuntimeException::class, "The model " . NormalUser::class . " must implement " . CipherSweetEncrypted::class);

it('creates an encryptedUnique rule via macro', function () {
    $rule = Rule::encryptedUnique(User::class, 'email');

    expect($rule)->toBeInstanceOf(EncryptedUniqueRule::class);
});
