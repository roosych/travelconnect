<?php

namespace App\Domain\Offers\Enums;

enum OfferStatus: string
{
    case Received  = 'received';
    case Reviewed  = 'reviewed';
    case Selected  = 'selected';
    case Rejected  = 'rejected';
    case Expired   = 'expired';
    case Withdrawn = 'withdrawn';

    public function supplierLabel(): string
    {
        return __('offers.status.supplier.'.$this->value);
    }

    public function operatorLabel(): string
    {
        return __('offers.status.operator.'.$this->value);
    }

    public function supplierBadgeClass(): string
    {
        return match($this) {
            self::Received  => 'badge-light-warning',
            self::Reviewed  => 'badge-light-info',
            self::Selected  => 'badge-light-success',
            self::Rejected,
            self::Expired,
            self::Withdrawn => 'badge-light-secondary',
        };
    }

    public function operatorBadgeClass(): string
    {
        return match($this) {
            self::Received  => 'badge-light-warning',
            self::Reviewed  => 'badge-light-info',
            self::Selected  => 'badge-light-success',
            self::Rejected  => 'badge-light-secondary',
            self::Expired,
            self::Withdrawn => 'badge-light-dark',
        };
    }
}
