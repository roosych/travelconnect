<?php

namespace App\Domain\Bookings\Enums;

enum BookingStatus: string
{
    case Confirmed       = 'confirmed';
    case AwaitingPayment = 'awaiting_payment';
    case Paid            = 'paid';
    case Rescheduled     = 'rescheduled';
    case InProgress      = 'in_progress';
    case Completed       = 'completed';
    case Cancelled       = 'cancelled';

    public function agencyLabel(): string
    {
        return __('bookings.status.agency.'.$this->value);
    }

    public function operatorLabel(): string
    {
        return __('bookings.status.operator.'.$this->value);
    }

    public function agencyBadgeClass(): string
    {
        return match($this) {
            self::Confirmed,
            self::Paid            => 'badge-light-success',
            self::AwaitingPayment => 'badge-light-warning',
            self::Rescheduled     => 'badge-light-info',
            self::InProgress      => 'badge-light-primary',
            self::Completed       => 'badge-light-dark',
            self::Cancelled       => 'badge-light-danger',
        };
    }

    public function operatorBadgeClass(): string
    {
        return match($this) {
            self::Confirmed,
            self::Paid            => 'badge-light-success',
            self::AwaitingPayment => 'badge-light-warning',
            self::Rescheduled     => 'badge-light-info',
            self::InProgress      => 'badge-light-primary',
            self::Completed,
            self::Cancelled       => 'badge-light-dark',
        };
    }
}
