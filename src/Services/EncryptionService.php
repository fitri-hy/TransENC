<?php

namespace TransENC\Services;

use TransENC\Services\KeyManager;
use TransENC\Support\PayloadHelper;
use TransENC\Services\NonceManager;
use TransENC\Services\PayloadSigner;
use TransENC\Exceptions\DecryptionException;
use TransENC\Exceptions\EncryptionException;

class EncryptionService
{
    protected KeyManager $keyManager;

    public function __construct(KeyManager $keyManager)
    {
        $this->keyManager = $keyManager;
    }

    public function encrypt(string $payload, string $clientId): string
    {
        $payload = PayloadHelper::compress($payload);
        $aesKey = $this->keyManager->generateTemporaryKey();
        $nonce = NonceManager::generate();

        $iv = random_bytes(16);
        $encryptedPayload = openssl_encrypt(
            $payload,
            'aes-256-gcm',
            $aesKey,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        $encryptedKey = $this->keyManager->encryptKey($aesKey, $clientId);
        $signature = PayloadSigner::sign($encryptedPayload . $nonce, $clientId);

        return json_encode([
            'key'       => $encryptedKey,
            'payload'   => base64_encode($encryptedPayload),
            'iv'        => base64_encode($iv),
            'tag'       => base64_encode($tag),
            'nonce'     => $nonce,
            'signature' => $signature,
        ]);
    }

    public function decrypt(string $encrypted, string $clientId): string
    {
        $data = json_decode($encrypted, true);

        if (!$data || !isset($data['key'], $data['payload'], $data['iv'], $data['tag'], $data['nonce'], $data['signature'])) {
            throw new DecryptionException("Invalid payload structure");
        }

        NonceManager::validateOrAbort($data['nonce']);

        $aesKey = $this->keyManager->decryptKey($data['key'], $clientId);
        $iv = base64_decode($data['iv']);
        $tag = base64_decode($data['tag']);
        $payloadEncrypted = base64_decode($data['payload']);

        if (!PayloadSigner::verify($payloadEncrypted . $data['nonce'], $data['signature'], $clientId)) {
            throw new DecryptionException("Signature verification failed");
        }

        $payload = openssl_decrypt(
            $payloadEncrypted,
            'aes-256-gcm',
            $aesKey,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($payload === false) {
            throw new DecryptionException("AES decryption failed");
        }

        return PayloadHelper::decompress($payload);
    }
}
