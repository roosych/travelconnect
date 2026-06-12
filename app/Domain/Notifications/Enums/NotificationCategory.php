<?php

namespace App\Domain\Notifications\Enums;

enum NotificationCategory: string
{
    // Client-facing (agency / supplier) — configurable in their settings matrix.
    case Request = 'request_status';
    case Rfq = 'rfq';
    case Proposal = 'proposal';
    case Booking = 'booking';
    case Offer = 'offer';

    // Operator-facing — always delivered (bell + email), not shown in the client matrix.
    case OperatorOffer = 'operator_offer';
    case OperatorProposal = 'operator_proposal';
    case OperatorRequest = 'operator_request';

    /** Which role this category is relevant to: 'agency' | 'supplier' | 'operator'. */
    public function audience(): string
    {
        return match ($this) {
            self::Request, self::Proposal, self::Booking => 'agency',
            self::Rfq, self::Offer => 'supplier',
            self::OperatorOffer, self::OperatorProposal, self::OperatorRequest => 'operator',
        };
    }

    /** @return list<self> categories relevant to the given audience. */
    public static function forAudience(string $audience): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self $c) => $c->audience() === $audience,
        ));
    }

    public function label(): string
    {
        return __('notifications.cat.'.$this->value.'.label');
    }

    public function description(): string
    {
        return __('notifications.cat.'.$this->value.'.desc');
    }

    /** Keenicon used for the in-app bell. */
    public function icon(): string
    {
        return match ($this) {
            self::Request => 'ki-document',
            self::Rfq, self::OperatorRequest => 'ki-questionnaire-tablet',
            self::Proposal, self::OperatorProposal => 'ki-discount',
            self::Booking => 'ki-calendar-tick',
            self::Offer, self::OperatorOffer => 'ki-handcart',
        };
    }
}
