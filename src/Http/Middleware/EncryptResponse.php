<?php

namespace TransENC\Http\Middleware;

use Closure;
use TransENC\Services\EncryptionService;
use TransENC\Exceptions\EncryptionException;

class EncryptResponse
{
    protected EncryptionService $encryptionService;

    public function __construct(EncryptionService $encryptionService)
    {
        $this->encryptionService = $encryptionService;
    }

    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if ($response->headers->has('Content-Type') &&
            str_contains($response->headers->get('Content-Type'), 'application/json') &&
            $request->header('X-Encrypted', false)
        ) {
            try {
                $payload = $response->getContent();
                $encrypted = $this->encryptionService->encrypt($payload);
                $response->setContent($encrypted);
            } catch (\Exception $e) {
                throw new EncryptionException($e->getMessage());
            }
        }

        return $response;
    }
}
