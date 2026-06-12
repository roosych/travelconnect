<?php

namespace App\Domain\Requests\Enums;

enum RequestStatus: string
{
    case Draft      = 'draft';
    case Submitted  = 'submitted';
    case Processing = 'processing';
    case Booked     = 'booked';
    case Completed  = 'completed';
    case Cancelled  = 'cancelled';

    public function agencyLabel(): string
    {
        return __('requests.status.agency.'.$this->value);
    }

    public function operatorLabel(): string
    {
        return __('requests.status.operator.'.$this->value);
    }

    public function agencyBadgeClass(): string
    {
        return match($this) {
            self::Draft      => 'badge-light-secondary',
            self::Submitted  => 'badge-light-primary',
            self::Processing => 'badge-light-info',
            self::Booked     => 'badge-light-success',
            self::Completed  => 'badge-light-dark',
            self::Cancelled  => 'badge-light-danger',
        };
    }

    public function operatorBadgeClass(): string
    {
        return match($this) {
            self::Draft      => 'badge-light-secondary',
            self::Submitted  => 'badge-light-info',
            self::Processing => 'badge-light-primary',
            self::Booked     => 'badge-light-success',
            self::Completed  => 'badge-light-dark',
            self::Cancelled  => 'badge-light-danger',
        };
    }
}
