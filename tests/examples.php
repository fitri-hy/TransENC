<?php

use Illuminate\Support\Facades\Route;
use TransENC\Services\EncryptionService;
use TransENC\Services\NonceManager;
use TransENC\Services\PayloadSigner;

Route::get('/transenc-full-test', function () {

    $clientId = 'testclient';
    $encryptor = app(EncryptionService::class);

    /* ------------------------------
       STEP 1 — Original Payload
    ------------------------------ */
    $payload = [
        'name' => 'FHY',
        'role' => 'admin',
        'time' => now()->timestamp,
    ];

    /* ------------------------------
       STEP 2 — Encrypt Payload
       (auto nonce + auto HMAC signature)
    ------------------------------ */
    $encryptedPayload = $encryptor->encrypt(json_encode($payload), $clientId);

    /* ------------------------------
       STEP 3 — Decrypt Payload
       (auto validate nonce + signature)
    ------------------------------ */
    $decryptedPayload = json_decode($encryptor->decrypt($encryptedPayload, $clientId), true);

    /* ------------------------------
       STEP 4 — Extract Nonce & Signature
    ------------------------------ */
    $payloadData = json_decode($encryptedPayload, true);
    $nonce = $payloadData['nonce'] ?? null;
    $signature = $payloadData['signature'] ?? null;

    /* ------------------------------
       STEP 5 — Verify Signature
    ------------------------------ */
    $signatureValid = false;
    if ($signature) {
        $signatureValid = PayloadSigner::verify($payloadData['payload'], $signature, $encryptor->getTemporaryKeyFromEncrypted($payloadData['key'], $clientId));
    }

    /* ------------------------------
       STEP 6 — Validate Nonce
    ------------------------------ */
    $nonceValid = $nonce ? NonceManager::verify($nonce) : null;

    /* ------------------------------
       STEP 7 — Prepare Response Payload
    ------------------------------ */
    $responsePayload = [
        'status' => 'success',
        'received' => $decryptedPayload,
    ];

    /* ------------------------------
       STEP 8 — Encrypt Response
       (auto nonce + signature)
    ------------------------------ */
    $encryptedResponse = $encryptor->encrypt(json_encode($responsePayload), $clientId);

    /* ------------------------------
       STEP 9 — Decrypt Response
    ------------------------------ */
    $decryptedResponse = json_decode($encryptor->decrypt($encryptedResponse, $clientId), true);

    /* ------------------------------
       STEP 10 — Return Debug Output
    ------------------------------ */
    return response()->json([
        'original_payload'       => $payload,
        'encrypted_payload'      => $encryptedPayload,
        'decrypted_payload'      => $decryptedPayload,
        'nonce'                  => $nonce,
        'signature'              => $signature,
        'signature_valid'        => $signatureValid,
        'nonce_valid'            => $nonceValid,
        'response_payload'       => $responsePayload,
        'encrypted_response'     => $encryptedResponse,
        'decrypted_response'     => $decryptedResponse,
    ]);
});
