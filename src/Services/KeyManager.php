<?php

namespace TransENC\Services;

use Illuminate\Support\Facades\File;
use RuntimeException;

class KeyManager
{
    protected string $keyPath;

    public function __construct()
    {
        $this->keyPath = config('encrypted_transport.key_path', storage_path('transenc/keys'));
        if (!File::exists($this->keyPath)) {
            File::makeDirectory($this->keyPath, 0755, true);
        }
    }

    public function generateTemporaryKey(): string
    {
        return random_bytes(32);
    }

    public function encryptKey(string $key, string $clientId): string
    {
        $publicKeyPath = $this->getPublicKeyPath($clientId);
        if (!File::exists($publicKeyPath)) {
            throw new RuntimeException("Public key not found for client {$clientId}");
        }
        $publicKey = File::get($publicKeyPath);
        openssl_public_encrypt($key, $encryptedKey, $publicKey);
        return base64_encode($encryptedKey);
    }

    public function decryptKey(string $encryptedKey, string $clientId): string
    {
        $privateKeyPath = $this->getPrivateKeyPath($clientId);
        if (!File::exists($privateKeyPath)) {
            throw new RuntimeException("Private key not found for client {$clientId}");
        }
        $privateKey = File::get($privateKeyPath);
        openssl_private_decrypt(base64_decode($encryptedKey), $decryptedKey, $privateKey);
        return $decryptedKey;
    }

    protected function getPublicKeyPath(string $clientId): string
    {
        return $this->keyPath . "/{$clientId}_public.pem";
    }

    protected function getPrivateKeyPath(string $clientId): string
    {
        return $this->keyPath . "/{$clientId}_private.pem";
    }
}
