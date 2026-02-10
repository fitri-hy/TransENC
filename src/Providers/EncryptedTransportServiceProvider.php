<?php

namespace TransENC\Providers;

use Illuminate\Support\ServiceProvider;
use TransENC\Http\Middleware\DecryptRequest;
use TransENC\Http\Middleware\EncryptResponse;
use TransENC\Console\GenerateClientKey;
use TransENC\Console\RotateKeys;
use TransENC\Console\EncryptPayload;

class EncryptedTransportServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/encrypted_transport.php', 'encrypted_transport');

        $this->app->singleton('TransENC\Services\KeyManager', function ($app) {
            return new \TransENC\Services\KeyManager();
        });

        $this->app->singleton('TransENC\Services\EncryptionService', function ($app) {
            return new \TransENC\Services\EncryptionService(
                $app->make('TransENC\Services\KeyManager')
            );
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateClientKey::class,
                RotateKeys::class,
                EncryptPayload::class,
            ]);
        }
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../Config/encrypted_transport.php' => config_path('encrypted_transport.php'),
        ], 'config');

        $router = $this->app['router'];
        $config = config('encrypted_transport.middleware');

        if ($config['decrypt_request'] ?? false) {
            $router->aliasMiddleware('transenc.decrypt', DecryptRequest::class);
        }

        if ($config['encrypt_response'] ?? false) {
            $router->aliasMiddleware('transenc.encrypt', EncryptResponse::class);
        }
    }
}
