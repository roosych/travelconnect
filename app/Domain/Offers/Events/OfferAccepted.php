<?php

namespace App\Domain\Offers\Events;

use App\Domain\Offers\Models\Offer;
use Illuminate\Foundation\Events\Dispatchable;

class OfferAccepted
{
    use Dispatchable;

    public function __construct(
        public readonly Offer $offer,
    ) {}
}
