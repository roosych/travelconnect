<?php

namespace App\Policies;

use App\Domain\RFQs\Models\Rfq;
use App\Domain\Users\Models\User;
use App\Policies\Concerns\BypassesForOperator;

class RfqPolicy
{
    use BypassesForOperator;

    public function view(User $user, Rfq $rfq): bool
    {
        if ($user->isAgency()) {
            $agencyIds = $user->agencies()->pluck('agencies.id');

            return $agencyIds->contains($rfq->request->agency_id);
        }

        if ($user->isSupplier()) {
            $supplierIds = $user->suppliers()->pluck('suppliers.id');

            return $rfq->suppliers()->whereIn('suppliers.id', $supplierIds)->exists();
        }

        return false;
    }
}
