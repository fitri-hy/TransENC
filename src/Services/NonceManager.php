<?php

namespace TransENC\Services;

use Illuminate\Support\Facades\Cache;

class NonceManager
{
    public static function generate(int $length = 16): string
    {
        return bin2hex(random_bytes($length));
    }
	
    public static function verify(string $nonce): bool
    {
        if (Cache::has($nonce)) {
            return false;
        }
        Cache::put($nonce, true, 300);
        return true;
    }

    public static function validateOrAbort(string $nonce): void
    {
        if (!self::verify($nonce)) {
            abort(419, 'Replay attack detected!');
        }
    }
}
