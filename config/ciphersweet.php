<?php

return [
    /**
     * This controls which cryptographic backend will be used by CipherSweet.
     * Unless you have specific compliance requirements, you should choose
     * "nacl".
     *
     * Supported: "boring", "fips", "nacl"
     */
    'backend' => env('CIPHERSWEET_BACKEND', 'nacl'),

    /**
     * Select which key provider your application will use. The default option
     * is to read a string literal out of .env, but it's also possible to
     * provide the key in a file or use random keys for testing.
     *
     * Supported: "file", "random", "string"
     */
    'provider' => env('CIPHERSWEET_PROVIDER', 'string'),

    /**
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
];
