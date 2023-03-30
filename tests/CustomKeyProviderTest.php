<?php

use Illuminate\Support\Facades\DB;
use ParagonIE\CipherSweet\Backend\Key\SymmetricKey;
use ParagonIE\CipherSweet\CipherSweet;
use ParagonIE\CipherSweet\Contract\KeyProviderInterface;
use ParagonIE\ConstantTime\Hex;
use Spatie\LaravelCipherSweet\Tests\TestClasses\User;

class CustomKeyProviderFactory {
    public function __invoke()
    {
        return new CustomKeyProvider();
    }
}

class InvalidKeyProviderFactory {
    public function __invoke()
    {
        return new self();
    }
}

class CustomKeyProvider implements KeyProviderInterface {

    public function getSymmetricKey(): SymmetricKey
    {
        return new SymmetricKey('123');
    }
}

it('can use a custom key provider', function () {
    config()->set('ciphersweet.provider', 'custom');
    config()->set('ciphersweet.providers.custom', CustomKeyProviderFactory::class);

    $this->app->forgetInstance(CipherSweet::class);

    $this->user = User::create([
        'name' => 'John Doe',
        'password' => bcrypt('password'),
        'email' => 'john@example.com',
    ]);

    expect(DB::table('users')->first()->email)->toStartWith('nacl:')
        ->and($this->user->email)->toEqual('john@example.com');
});

it('throws when the factory does not return a valid key provider', function () {
    config()->set('ciphersweet.provider', 'custom');
    config()->set('ciphersweet.providers.custom', InvalidKeyProviderFactory::class);

    $this->app->forgetInstance(CipherSweet::class);

    $this->expectExceptionMessage("InvalidKeyProviderFactory must implement " . KeyProviderInterface::class);

    User::create([
        'name' => 'John Doe',
        'password' => bcrypt('password'),
        'email' => 'john@example.com',
    ]);
});
