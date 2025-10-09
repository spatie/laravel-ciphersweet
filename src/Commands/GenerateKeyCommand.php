<?php

namespace Spatie\LaravelCipherSweet\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Database\Eloquent\Model;
use ParagonIE\ConstantTime\{
    Base64UrlSafe,
    Hex
};
use Spatie\LaravelCipherSweet\Contracts\CiphersweetEncrypted;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Helper\ProgressBar;

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
    public function handle() {
        $key = $this->generateRandomKey();

        if ($this->option('show')) {
            $this->line('<comment>' . $key . '</comment>');

            return;
        }

        if (!$this->replaceCurrentKey()) {
            return;
        }

        $this->reencryptExistingData($key);

        if (!$this->setKeyInEnvironmentFile($key)) {
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
    protected function generateRandomKey(): string {
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
    protected function generateRandomBytes(): string {
        return random_bytes(32);
    }

    /**
     * Confirm if the current key should be replaced.
     *
     * @return bool
     */
    protected function replaceCurrentKey(): bool {
        $currentKey = $this->laravel['config']['ciphersweet.providers.string.key'];

        if (strlen($currentKey) !== 0 && (!$this->confirmToProceed())) {
            return false;
        }
        return true;
    }

    /**
     * Set the CipherSweet key in the environment file.
     *
     * @param string $key
     * @return bool
     */
    protected function setKeyInEnvironmentFile($key): bool {
        if (!$this->writeNewEnvironmentFileWith($key)) {
            return false;
        }

        return true;
    }

    /**
     * Write a new environment file with the given CipherSweet key.
     *
     * @param string $key
     * @return bool
     */
    protected function writeNewEnvironmentFileWith($key): bool {
        $input = file_get_contents($this->laravel->environmentFilePath());

        if (!$this->hasKeyPattern($input)) {
            $replaced = $input . PHP_EOL . 'CIPHERSWEET_KEY=' . $key . PHP_EOL;
        } else {
            $replaced = preg_replace(
                $this->keyReplacementPattern(),
                'CIPHERSWEET_KEY=' . $key,
                $input
            );
        }

        if ($replaced === $input || $replaced === null) {
            $this->error('Unable to set CipherSweet key. No CIPHERSWEET_KEY variable was found in the .env file.');

            return false;
        }

        file_put_contents($this->laravel->environmentFilePath(), $replaced);

        return true;
    }

    /**
     * Check if environment file has a CIPHERSWEET_KEY variable.
     *
     * @param string $file_content
     * @return bool
     */
    protected function hasKeyPattern($file_content): bool {
        if (preg_match('/^CIPHERSWEET_KEY=/m', $file_content)) {
            return true;
        }
        return false;
    }

    /**
     * Get a regex pattern that will match env CIPHERSWEET_KEY with any random key.
     *
     * @return string
     */
    protected function keyReplacementPattern(): string {
        $escaped = preg_quote('=' . $this->laravel['config']['ciphersweet.providers.string.key'], '/');

        return "/^CIPHERSWEET_KEY{$escaped}/m";
    }

    /**
     * Re-encrypt existing data with the new key.
     *
     * @param $key
     * @return void
     */
    protected function reencryptExistingData($key): void {
        $modelClasses = $this->getModels();

        ProgressBar::setFormatDefinition('custom', ' %current%/%max% [%bar%] %message%');

        $progressBar = $this->output->createProgressBar(count($modelClasses));
        $progressBar->setFormat('custom');

        $progressBar->start();

        foreach ($modelClasses as $modelClass) {
            $progressBar->setMessage("Re-encrypting " . $modelClass);
            $progressBar->advance();

            try {
                $this->callSilent('ciphersweet:encrypt', [
                    'model' => $modelClass,
                    'newKey' => $key,
                ]);
            } catch (\Throwable $th) {
                $this->components->error("Error re-encrypting model {$modelClass}: " . $th->getMessage());
            }
        }

        $progressBar->finish();
    }

    /**
     * Get all model classes in the app/Models directory.
     *
     * @return array
     */
    protected function getModels(): array {
        $modelClasses = [];
        $modelPath = app_path('Models');
        $files = scandir($modelPath);
        foreach ($files as $file) {
            if (!preg_match('/^.*\.php$/', $file)) continue;

            $modelClass = 'App\\Models\\' . pathinfo($file, PATHINFO_FILENAME);
            if (!class_exists($modelClass)) continue;

            $model = (new $modelClass());
            if (!$model instanceof Model) continue;
            if (!$model instanceof CiphersweetEncrypted) continue;

            $modelClasses[] = 'App\\Models\\' . pathinfo($file, PATHINFO_FILENAME);
        }

        return $modelClasses;
    }
}