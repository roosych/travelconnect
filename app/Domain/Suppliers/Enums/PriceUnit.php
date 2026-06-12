<?php

namespace App\Domain\Suppliers\Enums;

enum PriceUnit: string
{
    case PerPerson  = 'per_person';
    case PerDay     = 'per_day';
    case PerNight   = 'per_night';
    case PerVehicle = 'per_vehicle';
    case PerGroup   = 'per_group';
    case Fixed      = 'fixed';

    public function label(): string
    {
        return match($this) {
            self::PerPerson  => 'per person',
            self::PerDay     => 'per day',
            self::PerNight   => 'per night',
            self::PerVehicle => 'per vehicle',
            self::PerGroup   => 'per group',
            self::Fixed      => 'fixed',
        };
    }
}
