<?php

namespace App\Domain\Notifications\Listeners;

use App\Domain\RFQs\Events\RfqSentToSupplier;
use App\Domain\RFQs\Notifications\RfqDispatchedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class SendRfqNotification
{
    public function handle(RfqSentToSupplier $event): void
    {
        $supplier = $event->supplier;
        $users = $supplier->users;

        // Portal supplier: notify its members per their own channel preferences.
        if ($users->isNotEmpty()) {
            Notification::send($users, new RfqDispatchedNotification($event->rfq));

            return;
        }

        // Non-portal supplier: email the contact with the signed magic link.
        if (! empty($supplier->email)) {
            $token = DB::table('rfq_supplier')
                ->where('rfq_id', $event->rfq->id)
                ->where('supplier_id', $supplier->id)
                ->value('token');

            Notification::route('mail', $supplier->email)
                ->notify(new RfqDispatchedNotification($event->rfq, $token));
        }
    }
}
