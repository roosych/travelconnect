<?php

namespace App\Console\Commands;

use App\Domain\Proposals\Enums\ProposalStatus;
use App\Domain\Proposals\Models\Proposal;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ExpireProposals extends Command
{
    protected $signature   = 'proposals:expire';
    protected $description = 'Mark sent proposals past their valid_until date as expired';

    public function handle(): int
    {
        $count = Proposal::where('status', ProposalStatus::Sent->value)
            ->whereNotNull('valid_until')
            ->where('valid_until', '<', Carbon::today())
            ->update(['status' => ProposalStatus::Expired->value]);

        $this->info("Expired {$count} proposal(s).");

        return self::SUCCESS;
    }
}
