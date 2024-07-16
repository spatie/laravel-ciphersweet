# Use CipherSweet in your Laravel project

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/laravel-ciphersweet.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-ciphersweet)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/spatie/laravel-ciphersweet/run-tests?label=tests)](https://github.com/spatie/laravel-ciphersweet/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/spatie/laravel-ciphersweet/Check%20&%20fix%20styling?label=code%20style)](https://github.com/spatie/laravel-ciphersweet/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-ciphersweet.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-ciphersweet)

In your project, you might store sensitive personal data in your database. Should an unauthorised person get access to your DB, all sensitive can be read which is obviously not good.

To solve this problem, you can encrypt the personal data. This way, unauthorized persons cannot read it, but your application can still decrypt it when you need to display or work with the data.

[CipherSweet](https://ciphersweet.paragonie.com/) is a backend library developed by [Paragon Initiative Enterprises](https://paragonie.com/) for implementing [searchable field-level encryption](https://paragonie.com/blog/2017/05/building-searchable-encrypted-databases-with-php-and-sql). It can encrypt and decrypt values in a very secure way. It is also able to create blind indexes. These indexes can be used to perform searches on encrypted data. The indexes themselves are unreadable by humans.

Our package is a wrapper over CipherSweet, which allows you to easily use it with Laravel's Eloquent models.

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/laravel-ciphersweet.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/laravel-ciphersweet)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

You can install the package via composer:

```bash
composer require spatie/laravel-ciphersweet
```

You must publish and run the migrations with:

```bash
php artisan vendor:publish --tag="ciphersweet-migrations"
php artisan migrate
```

Optionally, you can publish the config file with:

```bash
php artisan vendor:publish --tag="ciphersweet-config"
```

This is the contents of the config file:

```php
return [
    /*
     * This controls which cryptographic backend will be used by CipherSweet.
     * Unless you have specific compliance requirements, you should choose
     * "nacl".
     *
     * Supported: "boring", "fips", "nacl"
     */

    'backend' => env('CIPHERSWEET_BACKEND', 'nacl'),

    /*
     * Select which key provider your application will use. The default option
     * is to read a string literal out of .env, but it's also possible to
     * provide the key in a file or use random keys for testing.
     *
     * Supported: "file", "random", "string"
     */

    'provider' => env('CIPHERSWEET_PROVIDER', 'string'),

    /*
     * Set provider-specific options here. "string" will read the key directly
     * from your .env file. "file" will read the contents of the specified file
     * to use as your key. "custom" points to a factory class that returns a
     * provider from its `__invoke` method. Please see the docs for more details.
     */
    'providers' => [
        'file' => [
            'path' => env('CIPHERSWEET_FILE_PATH'),
        ],
        'string' => [
            'key' => env('CIPHERSWEET_KEY'),
        ],
    ],
    
    /*
     * The provided code snippet checks whether the $permitEmpty property is set to false
     * for a given field. If it is not set to false, it throws an EmptyFieldException indicating
     * that the field is not defined in the row. This ensures that the code enforces the requirement for
     * the field to have a value and alerts the user if it is empty or undefined.
     * Supported: "true", "false"
     */
    'permit_empty' => env('CIPHERSWEET_PERMIT_EMPTY', FALSE)

];
```

## Usage

Few steps are involved to store encrypted values. Let's go through them.

### 1. Preparing your model and choosing the attributes that should be encrypted

Add the `CipherSweetEncrypted` interface and `UsesCipherSweet` trait to the model that you want to add encrypted fields to.

You'll need to implement the `configureCipherSweet` method to configure CipherSweet.

```php
use Spatie\LaravelCipherSweet\Contracts\CipherSweetEncrypted;
use Spatie\LaravelCipherSweet\Concerns\UsesCipherSweet;
use ParagonIE\CipherSweet\EncryptedRow;
use ParagonIE\CipherSweet\BlindIndex;
use Illuminate\Database\Eloquent\Model;

class User extends Model implements CipherSweetEncrypted
{
    use UsesCipherSweet;
    
    /**
     * Encrypted Fields
     *
     * Each column that should be encrypted should be added below. Each column
     * in the migration should be a `text` type to store the encrypted value.
     *
     * ```
     * ->addField('column_name')
     * ->addBooleanField('column_name')
     * ->addIntegerField('column_name')
     * ->addTextField('column_name')
     * ```
     *
     * Optional Fields
     * 
     * These do not encrypt when NULL is provided as a value.
     * Instead, they become an unencrypted NULL.
     * 
     * ```
     * ->addOptionalTextField('column_name')
     * ->addOptionalBooleanField('column_name')
     * ->addOptionalFloatField('column_name')
     * ->addOptionalIntegerField('column_name')
     * ```
     * 
     * A JSON array can be encrypted as long as the key structure is defined in
     * a field map. See the docs for details on defining field maps.
     *
     * ```
     * ->addJsonField('column_name', $fieldMap)
     * ```
     *
     * Each field that should be searchable using an exact match needs to be
     * added as a blind index. Partial search is not supported. See the docs
     * for details on bit sizes and how to use compound indexes.
     *
     * ```
     * ->addBlindIndex('column_name', new BlindIndex('column_name_index'))
     * ```
     *
     * @see https://github.com/spatie/laravel-ciphersweet
     * @see https://ciphersweet.paragonie.com/
     * @see https://ciphersweet.paragonie.com/php/blind-index-planning
     * @see https://github.com/paragonie/ciphersweet/blob/master/src/EncryptedRow.php
     *
     * @param EncryptedRow $encryptedRow
     *
     * @return void
     */
    public static function configureCipherSweet(EncryptedRow $encryptedRow): void
    {
        $encryptedRow
            ->addField('email')
            ->addBlindIndex('email', new BlindIndex('email_index'));
    }
}
```

The example above will encrypt the `email` field on the `User` model. It also adds a blind index in the `blind_indexes` table which allows you to search on it.

[Check out the CipherSweet PHP docs](https://ciphersweet.paragonie.com/php) for more information on what is possible.

### 2. Generating the encrypting key

An encryption key is used to encrypt your values.  You can generate a new CipherSweet encrypting key using this command:

```bash
php artisan ciphersweet:generate-key
```

### 3. Updating your .env file

After the key has been generated, you should add the generated CipherSweet key to your .env file.

```text
CIPHERSWEET_KEY=<YOUR-KEY>
```

The key will be used by your application to manage encrypted values.

### 4. Encrypting model attributes

With this in place, you can run this command to encrypt all values:

```bash
php artisan ciphersweet:encrypt <your-model-class> <generated-key>
```

The command will update all the encrypted fields and blind indexes of the model.

If you have a lot of rows, this process can take a long time. The command is restartable: it can be re-run without needing to re-encrypt already rotated keys.


### Searching on blind indexes

Even though values are encrypted, you can still search them using a blind index. The blind indexes will have been built up when you ran the command to encrypt the model values.

This package provides a `whereBlind` and `orWhereBlind` scope to search on blind indexes.

The first parameter is the column, the second the index name you set up when calling `->addBlindIndex`, the third is the raw value, the package will automatically apply any transformations and hash the value to search on the blind index.

```php
$user = User::whereBlind('email', 'email_index', 'rias@spatie.be');
```

### Rotating keys

Should you suspect that somebody got a hold of your encrypting key, you can re-encrypt the values. Simply generate another encrypting key, and run the `php artisan ciphersweet:encrypt` command again.

```bash
php artisan ciphersweet:encrypt "App\User" <your-new-key>
```

This will update all the encrypted fields and blind indexes of the model. Once this is done, you can update your environment or config file to use the new key.

## Implementing a custom backend

You can implement a custom backend by setting the `ciphersweet.backend` config value to `custom`.

The `ciphersweet.backend.custom` config value must then be set to an invokeable factory class that returns an implementation of `ParagonIE\CipherSweet\Contract\BackendInterface`

```php
class CustomBackendFactory {
    public function __invoke()
    {
        return new CustomBackend();
    }
}

class CustomBackend implements BackendInterface {

    public function encrypt(string $plaintext, SymmetricKey $key, string $aad = ''): string
    {
        // Your logic here.
    }

    public function decrypt(string $ciphertext, SymmetricKey $key, string $aad = ''): string
    {
        // Your logic here.
    }

    public function blindIndexFast(string $plaintext, SymmetricKey $key, ?int $bitLength = null): string
    {
        // Your logic here.
    }

    public function blindIndexSlow(string $plaintext, SymmetricKey $key, ?int $bitLength = null, array $config = []): string
    {
        // Your logic here.
    }

    public function getIndexTypeColumn(string $tableName, string $fieldName, string $indexName): string
    {
        // Your logic here.
    }

    public function deriveKeyFromPassword(string $password, string $salt): SymmetricKey
    {
        // Your logic here.return new SymmetricKey('123');
    }

    public function doStreamDecrypt($inputFP, $outputFP, SymmetricKey $key, int $chunkSize = 8192, ?AAD $aad = null): bool
    {
        // Your logic here.
    }

    public function doStreamEncrypt($inputFP, $outputFP, SymmetricKey $key, int $chunkSize = 8192, string $salt = Constants::DUMMY_SALT, ?AAD $aad = null): bool
    {
        // Your logic here.
    }

    public function getFileEncryptionSaltOffset(): int
    {
        // Your logic here.
    }

    public function getPrefix(): string
    {
        // Your logic here.
    }
}
```

## Implementing a custom key provider

You can implement a custom key provider by setting the `ciphersweet.provider` config value to `custom`.

The `ciphersweet.providers.custom` config value must then be set to an invokeable factory class that returns an implementation of `ParagonIE\CipherSweet\Contract\KeyProviderInterface`  

```php
class CustomKeyProviderFactory {
    public function __invoke()
    {
        return new CustomKeyProvider();
    }
}

class CustomKeyProvider implements KeyProviderInterface {

    public function getSymmetricKey(): SymmetricKey
    {
        return new SymmetricKey(''); // Your logic here.
    }
}
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/spatie/.github/blob/main/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Rias Van der Veken](https://github.com/riasvdv)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
