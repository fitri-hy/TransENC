<?php

namespace TransENC\Support;

class PayloadHelper
{
    public static function compress(string $payload): string
    {
        return gzcompress($payload);
    }

    public static function decompress(string $payload): string
    {
        return gzuncompress($payload);
    }
}
