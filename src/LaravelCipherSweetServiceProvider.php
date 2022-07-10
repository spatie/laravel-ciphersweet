<?php

namespace Spatie\LaravelCipherSweet;

use ParagonIE\CipherSweet\Backend\BoringCrypto;
use ParagonIE\CipherSweet\Backend\FIPSCrypto;
use ParagonIE\CipherSweet\Backend\ModernCrypto;
use ParagonIE\CipherSweet\CipherSweet;
use ParagonIE\CipherSweet\Contract\BackendInterface;
use ParagonIE\CipherSweet\Contract\KeyProviderInterface;
use ParagonIE\CipherSweet\KeyProvider\FileProvider;
use ParagonIE\CipherSweet\KeyProvider\RandomProvider;
use ParagonIE\CipherSweet\KeyProvider\StringProvider;
use Spatie\LaravelCipherSweet\Commands\EncryptCommand;
use Spatie\LaravelCipherSweet\Commands\GenerateKeyCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelCipherSweetServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-ciphersweet')
            ->hasConfigFile()
            ->hasMigration('create_blind_indexes_table')
            ->hasCommands(GenerateKeyCommand::class, EncryptCommand::class);
    }

    public function packageRegistered()
    {
        $this->app->singleton(CipherSweet::class, function () {
            $backend = $this->buildBackend();

            return new CipherSweet($this->buildKeyProvider($backend), $backend);
        });
    }

    protected function buildBackend(): BackendInterface
    {
        return match (config('ciphersweet.backend')) {
            'fips' => new FIPSCrypto(),
            'boring' => new BoringCrypto(),
            default => new ModernCrypto(),
        };
    }

    protected function buildKeyProvider(BackendInterface $backend): KeyProviderInterface
    {
        return match (config('ciphersweet.provider')) {
            'file' => new FileProvider(config('ciphersweet.providers.file.path')),
            'string' => new StringProvider(config('ciphersweet.providers.string.key')),
            default => new RandomProvider($backend),
        };
    }
}
