<?php

namespace App\Domain\RFQs\Enums;

enum RfqStatus: string
{
    case Draft     = 'draft';
    case Sent      = 'sent';
    case Awaiting  = 'awaiting';
    case Closed    = 'closed';
    case Cancelled = 'cancelled';

    public function supplierLabel(): string
    {
        return __('rfqs.status.supplier.'.$this->value);
    }

    public function operatorLabel(): string
    {
        return __('rfqs.status.operator.'.$this->value);
    }

    public function supplierBadgeClass(): string
    {
        return match($this) {
            self::Draft              => 'badge-light-secondary',
            self::Sent, self::Awaiting => 'badge-light-primary',
            self::Closed             => 'badge-light-secondary',
            self::Cancelled          => 'badge-light-danger',
        };
    }

    public function operatorBadgeClass(): string
    {
        return match($this) {
            self::Draft     => 'badge-light-secondary',
            self::Sent      => 'badge-light-primary',
            self::Awaiting  => 'badge-light-info',
            self::Closed    => 'badge-light-warning',
            self::Cancelled => 'badge-light-danger',
        };
    }
}
