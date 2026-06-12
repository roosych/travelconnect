<?php

namespace App\Domain\Proposals\Events;

use App\Domain\Proposals\Models\Proposal;
use Illuminate\Foundation\Events\Dispatchable;

/** An agency accepted or rejected a proposal. */
class ProposalDecided
{
    use Dispatchable;

    public function __construct(
        public readonly Proposal $proposal,
        public readonly bool $accepted,
    ) {}
}
