<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Encryption Algorithm
    |--------------------------------------------------------------------------
    | AES for payload, RSA/ECC for key exchange
    */
    'payload_algorithm' => 'AES-256-CBC',
    'key_algorithm' => 'RSA-2048',

    /*
    |--------------------------------------------------------------------------
    | Key Storage
    |--------------------------------------------------------------------------
    | Can be file system, database, or external KMS
    */
    'key_storage' => env('TRANSENC_KEY_STORAGE', 'file'), // options: file, db, vault

    /*
    |--------------------------------------------------------------------------
    | Default Key Path
    |--------------------------------------------------------------------------
    */
    'key_path' => storage_path('transenc/keys'),

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    | Enable/disable request and response encryption middleware
    */
    'middleware' => [
        'decrypt_request' => true,
        'encrypt_response' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    | Enable logging of encrypted payload metadata (never log plaintext)
    */
    'logging' => true,
];
