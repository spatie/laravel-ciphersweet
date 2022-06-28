<?php

namespace Spatie\LaravelCiphersweet\Commands;

use Illuminate\Console\Command;

class LaravelCiphersweetCommand extends Command
{
    public $signature = 'laravel-ciphersweet';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
