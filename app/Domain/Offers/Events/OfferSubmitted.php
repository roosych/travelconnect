<?php

namespace App\Domain\Offers\Events;

use App\Domain\Offers\Models\Offer;
use Illuminate\Foundation\Events\Dispatchable;

/** A supplier submitted an offer in response to an RFQ. */
class OfferSubmitted
{
    use Dispatchable;

    public function __construct(
        public readonly Offer $offer,
    ) {}
}
