<?php

use Illuminate\Support\Facades\Route;
use TransENC\Services\EncryptionService;
use TransENC\Exceptions\DecryptionException;

Route::get('/transenc-full-test', function () {

    $clientId = 'testclient';
    $encryptor = app(EncryptionService::class);

    /*
    |--------------------------------------------------------------------------
    | CLIENT SIDE — Prepare Payload
    |--------------------------------------------------------------------------
    */
    $clientPayload = [
        'transaction_id' => 'TXN-' . strtoupper(bin2hex(random_bytes(5))),
        'user_id'        => 12345,
        'amount'         => 150000.75,
        'currency'       => 'IDR',
        'type'           => 'payment',
        'status'         => 'pending',
        'created_at'     => now()->toDateTimeString(),
        'metadata'       => [
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ],
    ];

    // Encrypt payload (client adds nonce & signature automatically)
    $encryptedPayload = $encryptor->encrypt(json_encode($clientPayload), $clientId);
    $encryptedPayloadData = json_decode($encryptedPayload, true);

    /*
    |--------------------------------------------------------------------------
    | SERVER SIDE — Receive & Decrypt Payload
    |--------------------------------------------------------------------------
    */
    try {
        $serverDecryptedPayload = json_decode($encryptor->decrypt($encryptedPayload, $clientId), true);
        $serverSignatureValid = true;
        $serverNonceValid = true;
    } catch (DecryptionException $e) {
        $serverDecryptedPayload = null;
        $serverSignatureValid = false;
        $serverNonceValid = false;
    }

    // Server prepares response payload
    $serverResponsePayload = [
        'status'   => 'success',
        'received' => $serverDecryptedPayload,
    ];

    // Encrypt response (server adds nonce & signature automatically)
    $encryptedResponse = $encryptor->encrypt(json_encode($serverResponsePayload), $clientId);
    $encryptedResponseData = json_decode($encryptedResponse, true);

    /*
    |--------------------------------------------------------------------------
    | CLIENT SIDE — Decrypt Response
    |--------------------------------------------------------------------------
    */
    try {
        $clientDecryptedResponse = json_decode($encryptor->decrypt($encryptedResponse, $clientId), true);
    } catch (DecryptionException $e) {
        $clientDecryptedResponse = null;
    }

    // Return full debug output
    return response()->json([
        // Client -> Server
        'client_original_payload'   => $clientPayload,
        'client_encrypted_payload'  => $encryptedPayloadData,

        // Server -> Client
        'server_decrypted_payload'  => $serverDecryptedPayload,
        'server_signature_valid'    => $serverSignatureValid,
        'server_nonce_valid'        => $serverNonceValid,
        'server_response_payload'   => $serverResponsePayload,
        'server_encrypted_response' => $encryptedResponseData,

        // Client receives response
        'client_decrypted_response' => $clientDecryptedResponse,
    ]);
});
