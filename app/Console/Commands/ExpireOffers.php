<?php

namespace App\Console\Commands;

use App\Domain\Offers\Enums\OfferStatus;
use App\Domain\Offers\Models\Offer;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ExpireOffers extends Command
{
    protected $signature   = 'offers:expire';
    protected $description = 'Mark received/reviewed offers past their valid_until date as expired';

    public function handle(): int
    {
        $count = Offer::whereIn('status', [OfferStatus::Received->value, OfferStatus::Reviewed->value])
            ->whereNotNull('valid_until')
            ->where('valid_until', '<', Carbon::today())
            ->update(['status' => OfferStatus::Expired->value]);

        $this->info("Expired {$count} offer(s).");

        return self::SUCCESS;
    }
}
