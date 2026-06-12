<?php

namespace App\Domain\Suppliers\Enums;

enum IncidentType: string
{
    case OfferWithdrawn = 'offer_withdrawn';

    public function label(): string
    {
        return match($this) {
            self::OfferWithdrawn => 'Предложение отозвано',
        };
    }
}
