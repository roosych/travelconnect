<?php

namespace App\Policies;

use App\Domain\Offers\Models\Offer;
use App\Domain\Users\Models\User;
use App\Policies\Concerns\BypassesForOperator;

class OfferPolicy
{
    use BypassesForOperator;

    public function view(User $user, Offer $offer): bool
    {
        if ($user->isSupplier()) {
            return $user->suppliers()->pluck('suppliers.id')->contains($offer->supplier_id);
        }

        if ($user->isAgency()) {
            $agencyIds = $user->agencies()->pluck('agencies.id');

            return $agencyIds->contains($offer->rfq->request->agency_id);
        }

        return false;
    }
}
