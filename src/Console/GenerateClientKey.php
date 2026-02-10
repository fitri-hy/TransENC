<?php
namespace TransENC\Console;

use Illuminate\Console\Command;
use phpseclib3\Crypt\RSA;

class GenerateClientKey extends Command
{
    protected $signature='transenc:generate-key {client}';
    protected $description='Generate new encryption key for a client';

    public function handle()
    {
        $client=$this->argument('client');
        $keyPath=config('encrypted_transport.key_path');
        if(!is_dir($keyPath)) mkdir($keyPath,0755,true);

        $privateKey=RSA::createKey(2048);
        $publicKey=$privateKey->getPublicKey()->toString('PKCS8');

        file_put_contents("{$keyPath}/{$client}_private.pem",$privateKey->toString('PKCS8'));
        file_put_contents("{$keyPath}/{$client}_public.pem",$publicKey);

        $this->info("Keys generated for client {$client}");
    }
}
