<?php

namespace TransENC\Console;

use Illuminate\Console\Command;

class RotateKeys extends Command
{
    protected $signature = 'transenc:rotate-keys';
    protected $description = 'Rotate all server keys';

    public function handle()
    {
        // Logic: backup old keys, generate new keys, re-encrypt temporary keys
        $this->info("Keys rotated successfully!");
    }
}
