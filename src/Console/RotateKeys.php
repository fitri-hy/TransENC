<?php
namespace TransENC\Console;

use Illuminate\Console\Command;
use TransENC\Services\KeyManager;
use Illuminate\Support\Facades\File;

class RotateKeys extends Command
{
    protected $signature='transenc:rotate-keys';
    protected $description='Rotate all client keys and re-encrypt their payloads';

    public function handle(KeyManager $keyManager)
    {
        $path=config('encrypted_transport.key_path');
        $files=File::files($path);
        $clients=[];

        foreach($files as $file){
            if(str_ends_with($file->getFilename(),'_private.pem')){
                $clients[]=str_replace('_private.pem','',$file->getFilename());
            }
        }

        foreach($clients as $client){
            $keyManager->rotateKey($client);
        }

        $this->info("All client keys rotated successfully.");
    }
}
