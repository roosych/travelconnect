<?php

namespace App\Domain\Proposals\Events;

use App\Domain\Proposals\Models\Proposal;
use Illuminate\Foundation\Events\Dispatchable;

class ProposalSent
{
    use Dispatchable;

    public function __construct(
        public readonly Proposal $proposal,
    ) {}
}
