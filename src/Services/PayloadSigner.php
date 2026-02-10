<?php

namespace TransENC\Services;

class PayloadSigner
{
    public static function sign(string $payload, string $key): string
    {
        return hash_hmac('sha256', $payload, $key);
    }

    public static function verify(string $payload, string $signature, string $key): bool
    {
        return hash_equals($signature, self::sign($payload, $key));
    }
}
