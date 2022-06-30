<?php

namespace Spatie\LaravelCipherSweet\Commands;

use Illuminate\Console\Command;
use ParagonIE\ConstantTime\Hex;

class GenerateKeyCommand extends Command
{
    protected $signature = 'ciphersweet:generate-key';

    protected $description = 'Generate a CipherSweet encryption key';

    public function handle()
    {
        $encryptionKey = Hex::encode(random_bytes(32));

        $this->info('Here is the your new encryption key');
        $this->info('');
        $this->info($encryptionKey);
        $this->info('');
        $this->info('First, you should encrypt your model values using this command');
        $this->info("ciphersweet:encrypt <MODEL-CLASS> {$encryptionKey}");
        $this->info('');
        $this->info('Next, you should add this line to your .env file');
        $this->info("CIPHERSWEET_KEY={$encryptionKey}");
    }
}
