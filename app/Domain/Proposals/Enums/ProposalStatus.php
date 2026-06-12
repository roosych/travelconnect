<?php

namespace App\Domain\Proposals\Enums;

enum ProposalStatus: string
{
    case Draft     = 'draft';
    case Sent      = 'sent';
    case Accepted  = 'accepted';
    case Rejected  = 'rejected';
    case Expired   = 'expired';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return __('requests.proposal_status.'.$this->value);
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Draft     => 'badge-light-secondary',
            self::Sent      => 'badge-light-primary',
            self::Accepted  => 'badge-light-success',
            self::Rejected  => 'badge-light-danger',
            self::Expired   => 'badge-light-dark',
            self::Cancelled => 'badge-light-secondary',
        };
    }
}
