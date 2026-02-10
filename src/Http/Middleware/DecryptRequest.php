<?php

namespace TransENC\Http\Middleware;

use Closure;
use TransENC\Services\EncryptionService;
use TransENC\Exceptions\DecryptionException;

class DecryptRequest
{
    protected $encryptionService;

    public function __construct(EncryptionService $encryptionService)
    {
        $this->encryptionService = $encryptionService;
    }

    public function handle($request, Closure $next)
    {
        if ($request->isJson() && $request->header('X-Encrypted', false)) {
            $payload = $request->getContent();

            try {
                $decrypted = $this->encryptionService->decrypt($payload);
                $request->replace(json_decode($decrypted, true));
            } catch (\Exception $e) {
                throw new DecryptionException();
            }
        }

        return $next($request);
    }
}
