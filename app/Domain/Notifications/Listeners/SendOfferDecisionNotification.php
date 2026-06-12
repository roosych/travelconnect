<?php

namespace App\Domain\Notifications\Listeners;

use App\Domain\Offers\Events\OfferAccepted;
use App\Domain\Offers\Events\OfferRejected;
use App\Domain\Offers\Models\Offer;
use App\Domain\Offers\Notifications\OfferDecisionNotification;
use Illuminate\Support\Facades\Notification;

class SendOfferDecisionNotification
{
    public function accepted(OfferAccepted $event): void
    {
        $this->notify($event->offer, true);
    }

    public function rejected(OfferRejected $event): void
    {
        $this->notify($event->offer, false);
    }

    private function notify(Offer $offer, bool $accepted): void
    {
        $offer->loadMissing('supplier.users', 'rfq');

        $supplier = $offer->supplier;
        if ($supplier === null) {
            return;
        }

        $users = $supplier->users;

        if ($users->isNotEmpty()) {
            Notification::send($users, new OfferDecisionNotification($offer, $accepted));

            return;
        }

        if (! empty($supplier->email)) {
            Notification::route('mail', $supplier->email)
                ->notify(new OfferDecisionNotification($offer, $accepted));
        }
    }
}
