<?php

use Illuminate\Support\Facades\Route;
use TransENC\Services\EncryptionService;
use TransENC\Services\PayloadSigner;
use TransENC\Services\NonceManager;

Route::get('/transenc-full-test', function () {

    $clientId  = 'testclient';
    $encryptor = app(EncryptionService::class);
    $signer    = app(PayloadSigner::class);

    /* ----------------------------------------------------
     | STEP 1 — Original Payload (Client Side)
     ---------------------------------------------------- */
    $step_1 = [
        'name' => 'FHY',
        'role' => 'admin',
        'time' => now()->timestamp,
    ];

    /* ----------------------------------------------------
     | STEP 2 — Attach Nonce (Anti Replay)
     ---------------------------------------------------- */
    $nonce  = bin2hex(random_bytes(16));
    $step_2 = [...$step_1, 'nonce' => $nonce];
    $jsonPayload = json_encode($step_2);

    /* ----------------------------------------------------
     | STEP 3 — Sign Payload (Integrity)
     ---------------------------------------------------- */
    $step_3 = $signer->sign($jsonPayload, $clientId);

    /* ----------------------------------------------------
     | STEP 4 — Encrypt Payload (AES + RSA Hybrid)
     ---------------------------------------------------- */
    $step_4 = $encryptor->encrypt($jsonPayload, $clientId);

    /* ----------------------------------------------------
     | STEP 5 — Decrypt Payload (Server Side)
     ---------------------------------------------------- */
    $step_5 = json_decode(
        $encryptor->decrypt($step_4, $clientId),
        true
    );

    /* ----------------------------------------------------
     | STEP 6 — Verify Signature
     ---------------------------------------------------- */
    $step_6 = $signer->verify(
        json_encode($step_5),
        $step_3,
        $clientId
    );

    /* ----------------------------------------------------
     | STEP 7 — Validate Nonce (Replay Protection)
     ---------------------------------------------------- */
    $step_7 = NonceManager::verify($step_5['nonce']);

    if (!$step_7) {
        abort(409, 'Replay attack detected');
    }

    /* ----------------------------------------------------
     | STEP 8 — Prepare Response Payload
     ---------------------------------------------------- */
    $step_8 = [
        'status'   => 'success',
        'received' => $step_5,
        'nonce'    => bin2hex(random_bytes(16)),
    ];
    $jsonResponse = json_encode($step_8);

    /* ----------------------------------------------------
     | STEP 9 — Encrypt Response
     ---------------------------------------------------- */
    $step_9 = $encryptor->encrypt($jsonResponse, $clientId);

    /* ----------------------------------------------------
     | STEP 10 — Decrypt Response (Client Side)
     ---------------------------------------------------- */
    $step_10 = json_decode(
        $encryptor->decrypt($step_9, $clientId),
        true
    );

    /* ----------------------------------------------------
     | OUTPUT — All Steps (Debug)
     ---------------------------------------------------- */
    return response()->json([
        'step_1_original_payload'   => $step_1,
        'step_2_payload_with_nonce' => $step_2,
        'step_3_signature'          => $step_3,
        'step_4_encrypted_payload'  => $step_4,
        'step_5_decrypted_payload'  => $step_5,
        'step_6_signature_valid'    => $step_6,
        'step_7_nonce_valid'        => $step_7,
        'step_8_response_payload'   => $step_8,
        'step_9_encrypted_response' => $step_9,
        'step_10_decrypted_response'=> $step_10,
    ]);
});



/* ----------------------------------------------------
   | Response
   ----------------------------------------------------
{
  "step_1_original_payload": {
    "name": "FHY",
    "role": "admin",
    "time": 1770692840
  },
  "step_2_payload_with_nonce": {
    "name": "FHY",
    "role": "admin",
    "time": 1770692840,
    "nonce": "e46..."
  },
  "step_3_signature": "8e16...",
  "step_4_encrypted_payload": "{\"key\":\"JWEb..."}",
  "step_5_decrypted_payload": {
    "name": "FHY",
    "role": "admin",
    "time": 1770692840,
    "nonce": "e46..."
  },
  "step_6_signature_valid": true,
  "step_7_nonce_valid": true,
  "step_8_response_payload": {
    "status": "success",
    "received": {
      "name": "FHY",
      "role": "admin",
      "time": 1770692840,
      "nonce": "e46..."
    },
    "nonce": "5f4..."
  },
  "step_9_encrypted_response": "{\"key\":\"ceE..."}",
  "step_10_decrypted_response": {
    "status": "success",
    "received": {
      "name": "FHY",
      "role": "admin",
      "time": 1770692840,
      "nonce": "e46..."
    },
    "nonce": "5f4..."
  }
}
*/