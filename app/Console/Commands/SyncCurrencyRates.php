<?php

namespace App\Console\Commands;

use App\Domain\Settings\Services\CurrencyRateService;
use Illuminate\Console\Command;

class SyncCurrencyRates extends Command
{
    protected $signature   = 'currencies:sync-rates';
    protected $description = 'Fetch daily exchange rates from CBAR and update currencies table';

    public function handle(CurrencyRateService $service): int
    {
        $this->info('Fetching exchange rates from CBAR...');

        $result = $service->sync();

        if (! $result['success']) {
            $this->error($result['message']);
            return self::FAILURE;
        }

        $this->info("Updated {$result['updated']} currencies for {$result['date']}.");
        return self::SUCCESS;
    }
}
