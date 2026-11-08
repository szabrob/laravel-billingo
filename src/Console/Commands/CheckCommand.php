<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Szabrob\LaravelBillingo\Billingo;
use Szabrob\LaravelBillingo\Enums\Feature;
use Szabrob\LaravelBillingo\Exceptions\BillingoException;

class CheckCommand extends Command
{
    /**
     * The command signature.
     */
    protected $signature = 'billingo:check
                            {--key= : Check this API key instead of the configured one}';

    /**
     * The command description.
     */
    protected $description = 'Check the Billingo API connection and API key';

    /**
     * Execute the console command.
     */
    public function handle(Billingo $billingo): int
    {
        $key = $this->option('key');

        if (is_string($key) && $key !== '') {
            $billingo = $billingo->withApiKey($key);
        }

        try {
            $organization = $billingo->organization()->get();
        } catch (BillingoException $exception) {
            $status = $exception->response?->status();

            $this->components->error($status === null
                ? $exception->getMessage()
                : sprintf('Billingo API request failed (HTTP %d): %s', $status, $exception->getMessage()));

            return self::FAILURE;
        } catch (ConnectionException $exception) {
            $this->components->error('Could not connect to the Billingo API: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->components->info('The Billingo API connection is working.');

        $features = array_map(fn (Feature $feature): string => $feature->value, $organization->subscription->features ?? []);

        $this->components->twoColumnDetail('Tax code', $organization->taxCode ?: '-');
        $this->components->twoColumnDetail('Subscription expires', $organization->subscription?->expirationDate?->toDateString() ?? '-');
        $this->components->twoColumnDetail('Features', $features === [] ? '-' : implode(', ', $features));
        $this->components->twoColumnDetail('NAV connection', $organization->hasNavConnection ? 'Yes' : 'No');

        return self::SUCCESS;
    }
}
