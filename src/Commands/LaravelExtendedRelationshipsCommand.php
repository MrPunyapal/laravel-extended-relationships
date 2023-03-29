<?php

namespace Mrpunyapal\LaravelExtendedRelationships\Commands;

use Illuminate\Console\Command;

class LaravelExtendedRelationshipsCommand extends Command
{
    public $signature = 'laravel-extended-relationships';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
