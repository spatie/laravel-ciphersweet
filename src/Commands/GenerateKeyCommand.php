<?php

namespace Spatie\LaravelCipherSweet\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use ParagonIE\ConstantTime\{
    Base64UrlSafe,
    Hex
};
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'ciphersweet:generate-key')]
class GenerateKeyCommand extends Command
{
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ciphersweet:generate-key
        {--show : Display the CipherSweet key instead of modifying files}
        {--base64 : Generate key in base64 safe format}
        {--force : Force the operation to run when in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set the CipherSweet key';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $key = $this->generateRandomKey();

        if ($this->option('show')) {
            $this->line('<comment>' . $key . '</comment>');

            return;
        }

        if (! $this->setKeyInEnvironmentFile($key)) {
            return;
        }

        $this->laravel['config']['ciphersweet.providers.string.key'] = $key;

        $this->components->info('CipherSweet key set successfully.');
    }

    /**
     * Generate a random CipherSweet key.
     *
     * @return string
     */
    protected function generateRandomKey(): string
    {
        $randomBytes = $this->generateRandomBytes();

        return $this->option('base64')
           ? Base64UrlSafe::encode($randomBytes)
           : Hex::encode($randomBytes);
    }

    /**
     * Generate random bytes for key
     *
     * @return string
     */
    protected function generateRandomBytes(): string
    {
        return random_bytes(32);
    }

    /**
     * Set the CipherSweet key in the environment file.
     *
     * @param  string  $key
     * @return bool
     */
    protected function setKeyInEnvironmentFile($key): bool
    {
        $currentKey = $this->laravel['config']['ciphersweet.providers.string.key'];

        if (strlen($currentKey) !== 0 && (! $this->confirmToProceed())) {
            return false;
        }

        if (! $this->writeNewEnvironmentFileWith($key)) {
            return false;
        }

        return true;
    }

    /**
     * Write a new environment file with the given CipherSweet key.
     *
     * @param  string  $key
     * @return bool
     */
    protected function writeNewEnvironmentFileWith($key)
    {
        $replaced = preg_replace(
            $this->keyReplacementPattern(),
            'CIPHERSWEET_KEY=' . $key,
            $input = file_get_contents($this->laravel->environmentFilePath())
        );

        if ($replaced === $input || $replaced === null) {
            $this->error('Unable to set CipherSweet key. No CIPHERSWEET_KEY variable was found in the .env file.');

            return false;
        }

        file_put_contents($this->laravel->environmentFilePath(), $replaced);

        return true;
    }

    /**
     * Get a regex pattern that will match env CIPHERSWEET_KEY with any random key.
     *
     * @return string
     */
    protected function keyReplacementPattern()
    {
        $escaped = preg_quote('=' . $this->laravel['config']['ciphersweet.providers.string.key'], '/');

        return "/^CIPHERSWEET_KEY{$escaped}/m";
    }
}
