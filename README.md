
[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/support-ukraine.svg?t=1" />](https://supportukrainenow.org)

# Use CipherSweet in your Laravel project

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/laravel-ciphersweet.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-ciphersweet)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/spatie/laravel-ciphersweet/run-tests?label=tests)](https://github.com/spatie/laravel-ciphersweet/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/spatie/laravel-ciphersweet/Check%20&%20fix%20styling?label=code%20style)](https://github.com/spatie/laravel-ciphersweet/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-ciphersweet.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-ciphersweet)

CipherSweet is a backend library developed by [Paragon Initiative Enterprises](https://paragonie.com/) for implementing [searchable field-level encryption](https://paragonie.com/blog/2017/05/building-searchable-encrypted-databases-with-php-and-sql). This is a small Laravel wrapper package around it to improve developer experience.

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/laravel-ciphersweet.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/laravel-ciphersweet)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

You can install the package via composer:

```bash
composer require spatie/laravel-ciphersweet
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="laravel-ciphersweet-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-ciphersweet-config"
```

## Usage

Add the `CipherSweetEncrypted` interface and `UsesCipherSweet` trait to the model that you want to add encrypted fields to.

You'll need to implement the `configureCipherSweet` method to configure CipherSweet.

```php
use Spatie\LaravelCipherSweet\Contracts\CipherSweetEncrypted;
use Spatie\LaravelCipherSweet\Concerns\UsesCipherSweet;
use ParagonIE\CipherSweet\EncryptedRow;
use Illuminate\Database\Eloquent\Model;

class User extends Model implements CipherSweetEncrypted
{
    use UsesCipherSweet;
    
    public static function configureCipherSweet(EncryptedRow $encryptedRow): void
    {
        $encryptedRow
            ->addField('email')
            ->addBlindIndex('email', new BlindIndex('email_index'));
    }
}
```

The example above will encrypt the `email` field on the `User` model. It also adds a blind index in the `blind_indexes` table which allows you to search on it.

### Searching on blind indexes

This package provides a `whereBlind` and `orWhereBlind` scope to search on blind indexes.

The first parameter is the column, the second the index name you set up when calling `->addBlindIndex`, the third is the raw value, the package will automatically apply any transformations and hash the value to search on the blind index.

```php
$user = User::whereBlind('email', 'email_index', 'rias@spatie.be');
```

### Rotating keys

This package provides a `RotateModelEncryptionCommand` to rotate the encryption key.

You can generate a new key using:

```php
\ParagonIE\ConstantTime\Hex::encode(random_bytes(32))
```

Once you have a new key, you can call the command:

```shell
php artisan ciphersweet:rotate-model-encryption "App\User" <your-new-key>
```

This will update all the encrypted fields and blind indexes of the model. Once this is done, you can update your environment or config file to use the new key.

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
