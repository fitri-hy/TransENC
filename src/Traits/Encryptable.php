<?php

namespace TransENC\Traits;

use TransENC\Services\EncryptionService;

trait Encryptable
{
    public function setAttributeEncrypted($key, $value)
    {
        $service = app(EncryptionService::class);
        $this->attributes[$key] = $service->encrypt($value);
    }

    public function getAttributeDecrypted($key)
    {
        $service = app(EncryptionService::class);
        return $service->decrypt($this->attributes[$key]);
    }
}
