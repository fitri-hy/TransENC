<?php
namespace TransENC\Console;

use Illuminate\Console\Command;
use TransENC\Services\EncryptionService;

class EncryptPayload extends Command
{
    protected $signature='transenc:encrypt-payload {client} {payload}';
    protected $description='Encrypt sample payload for a client';

    public function handle(EncryptionService $service)
    {
        $client=$this->argument('client');
        $payload=$this->argument('payload');
        $this->line($service->encrypt($payload,$client));
    }
}
