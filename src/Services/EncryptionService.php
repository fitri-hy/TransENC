<?php

namespace TransENC\Services;

use TransENC\Services\KeyManager;
use TransENC\Support\PayloadHelper;

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

        $encryptedPayload = openssl_encrypt(
            $payload,
            'AES-256-CBC',
            $aesKey,
            0,
            $this->keyManager->iv()
        );

        $encryptedKey = $this->keyManager->encryptKey($aesKey, $clientId);

        return json_encode([
            'key' => $encryptedKey,
            'payload' => $encryptedPayload
        ]);
    }

    public function decrypt(string $encrypted, string $clientId): string
    {
        $data = json_decode($encrypted, true);
        $aesKey = $this->keyManager->decryptKey($data['key'], $clientId);

        $payload = openssl_decrypt(
            $data['payload'],
            'AES-256-CBC',
            $aesKey,
            0,
            $this->keyManager->iv()
        );

        return PayloadHelper::decompress($payload);
    }
}
