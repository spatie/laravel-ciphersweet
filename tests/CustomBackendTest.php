<?php

use Illuminate\Support\Facades\DB;
use ParagonIE\CipherSweet\Backend\Key\SymmetricKey;
use ParagonIE\CipherSweet\CipherSweet;
use ParagonIE\CipherSweet\Contract\BackendInterface;
use Spatie\LaravelCipherSweet\Tests\TestClasses\User;

class CustomBackendFactory
{
    public function __invoke()
    {
        return new CustomBackend();
    }
}

class InvalidBackendFactory
{
    public function __invoke()
    {
        return new self();
    }
}

class CustomBackend implements BackendInterface
{
    public function encrypt(
        string $plaintext,
        SymmetricKey $key,
        string $aad = ''
    ): string {
        return 'nacl:123';
    }

    public function decrypt(
        string $ciphertext,
        SymmetricKey $key,
        string $aad = ''
    ): string
    {
        return 'john@example.com';
    }

    public function blindIndexFast(
        string $plaintext,
        SymmetricKey $key,
        ?int $bitLength = null
    ): string {
        return '123';
    }

    public function blindIndexSlow(
        string $plaintext,
        SymmetricKey $key,
        ?int $bitLength = null,
        array $config = []
    ): string {
        return '123';
    }

    public function getIndexTypeColumn(
        string $tableName,
        string $fieldName,
        string $indexName
    ): string {
        return '123';
    }

    public function deriveKeyFromPassword(
        string $password,
        string $salt
    ): SymmetricKey {
        return new SymmetricKey('123');
    }

    public function doStreamDecrypt(
        $inputFP,
        $outputFP,
        SymmetricKey $key,
        int $chunkSize = 8192
    ): bool {
        return true;
    }

    public function doStreamEncrypt(
        $inputFP,
        $outputFP,
        SymmetricKey $key,
        int $chunkSize = 8192,
        string $salt = Constants::DUMMY_SALT
    ): bool {
        return true;
    }

    public function getFileEncryptionSaltOffset(): int
    {
        return 123;
    }

    public function getPrefix(): string
    {
        return '123';
    }
}

it('can use a custom backend', function () {
    config()->set('ciphersweet.backend', 'custom');
    config()->set('ciphersweet.backends.custom', CustomBackendFactory::class);

    $this->app->forgetInstance(CipherSweet::class);

    $this->user = User::create([
        'name' => 'John Doe',
        'password' => bcrypt('password'),
        'email' => 'john@example.com',
    ]);

    expect(DB::table('users')->first()->email)->toStartWith('nacl:')
        ->and($this->user->email)->toEqual('john@example.com');
});

it('throws when the factory does not return a valid backend', function () {
    config()->set('ciphersweet.backend', 'custom');
    config()->set('ciphersweet.backends.custom', InvalidBackendFactory::class);

    $this->app->forgetInstance(CipherSweet::class);

    $this->expectExceptionMessage("InvalidBackendFactory must implement " . BackendInterface::class);

    User::create([
        'name' => 'John Doe',
        'password' => bcrypt('password'),
        'email' => 'john@example.com',
    ]);
});
