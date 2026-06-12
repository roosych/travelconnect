<?php

namespace App\Domain\Notifications\Listeners;

use App\Domain\Offers\Events\OfferSubmitted;
use App\Domain\Offers\Notifications\OfferSubmittedNotification;
use Illuminate\Support\Facades\Notification;

class SendOperatorOfferNotification
{
    public function handle(OfferSubmitted $event): void
    {
        $event->offer->loadMissing('rfq.operator', 'supplier');

        $operator = $event->offer->rfq?->operator;
        if ($operator === null) {
            return;
        }

        Notification::send($operator, new OfferSubmittedNotification($event->offer));
    }
}
