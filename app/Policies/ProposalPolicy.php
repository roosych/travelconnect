<?php

namespace App\Policies;

use App\Domain\Proposals\Enums\ProposalStatus;
use App\Domain\Proposals\Models\Proposal;
use App\Domain\Users\Models\User;
use App\Policies\Concerns\BypassesForOperator;

class ProposalPolicy
{
    use BypassesForOperator;

    public function view(User $user, Proposal $proposal): bool
    {
        if ($user->isAgency()) {
            $agencyIds = $user->agencies()->pluck('agencies.id');

            return $agencyIds->contains($proposal->request->agency_id)
                && in_array($proposal->status, [ProposalStatus::Sent, ProposalStatus::Accepted, ProposalStatus::Rejected]);
        }

        return false;
    }

    public function decide(User $user, Proposal $proposal): bool
    {
        if (! $user->isAgency()) {
            return false;
        }

        $agencyIds = $user->agencies()->pluck('agencies.id');

        return $agencyIds->contains($proposal->request->agency_id)
            && $proposal->status === ProposalStatus::Sent
            && ! $proposal->is_expired;
    }
}
