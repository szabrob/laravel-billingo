<?php

declare(strict_types=1);

namespace LaravelBillingo\Billingo\Console\Commands;

use Illuminate\Console\Command;

class BillingoCommand extends Command
{
    /**
     * The command signature.
     */
    protected $signature = 'laravel-billingo:placeholder';

    /**
     * The command description.
     */
    protected $description = 'Placeholder Artisan command shipped by the package laravel-billingo.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->line('Billingo placeholder command executed.');

        return self::SUCCESS;
    }
}
