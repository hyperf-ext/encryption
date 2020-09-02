<?php

declare(strict_types=1);

return [

    'default' => 'aes',

    'driver' => [

        'aes' => [
            'class' => \HyperfExt\Encryption\Driver\AesDriver::class,
            'options' => [
                'key' => env('AES_KEY', ''),
                'cipher' => env('AES_CIPHER', 'AES-128-CBC'),
            ],
        ],

    ],

];
