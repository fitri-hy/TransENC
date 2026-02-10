<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Encryption Algorithm
    |--------------------------------------------------------------------------
    | AES-GCM untuk payload, RSA-2048 untuk key exchange
    */
    'payload_algorithm' => 'AES-256-GCM', // another option: AES-128-GCM, AES-192-GCM
    'key_algorithm' => 'RSA-2048',        // another option: RSA-4096, ECC-256

    /*
    |--------------------------------------------------------------------------
    | Key Storage
    |--------------------------------------------------------------------------
    | Pilihan: file (default), db, vault/KMS
    */
    'key_storage' => env('TRANSENC_KEY_STORAGE', 'file'),

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
    | Enable request/response encryption middleware
    */
    'middleware' => [
        'decrypt_request' => true,
        'encrypt_response' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    | Log metadata payload (never log plaintext)
    */
    'logging' => true,

    /*
    |--------------------------------------------------------------------------
    | Key Rotation Options
    |--------------------------------------------------------------------------
    | Grace period in seconds for old keys
    */
    'key_rotation' => [
        'enabled' => true,
        'grace_period' => 3600, // 1 hour
    ],

    /*
    |--------------------------------------------------------------------------
    | Nonce Options
    |--------------------------------------------------------------------------
    | Expiry time in seconds
    */
    'nonce' => [
        'length' => 16,   // 16 bytes = 32 hex chars
        'ttl'    => 300,  // valid for 5 minutes
    ],
];
