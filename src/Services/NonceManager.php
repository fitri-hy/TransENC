<?php

namespace TransENC\Services;

use Illuminate\Support\Facades\Cache;
use TransENC\Exceptions\DecryptionException;

class NonceManager
{
    public static function generate(int $length = 16): string
    {
        return bin2hex(random_bytes($length));
    }

    public static function validate(string $nonce): bool
    {
        if (Cache::has($nonce)) {
            return false;
        }

        Cache::put($nonce, true, 300);
        return true;
    }

    public static function validateOrAbort(string $nonce): void
    {
        if (!self::validate($nonce)) {
            throw new DecryptionException("Replay attack detected");
        }
    }
}
