<?php

namespace App\Domain\Notifications\Listeners;

use App\Domain\Requests\Events\RequestStatusChanged;
use App\Domain\Requests\Notifications\RequestStatusNotification;
use Illuminate\Support\Facades\Notification;

class SendRequestStatusNotification
{
    public function handle(RequestStatusChanged $event): void
    {
        $event->request->loadMissing('agency.users');

        $agency = $event->request->agency;
        if ($agency === null) {
            return;
        }

        $users = $agency->users;
        $notification = new RequestStatusNotification($event->request, $event->status);

        if ($users->isNotEmpty()) {
            Notification::send($users, $notification);

            return;
        }

        if (! empty($agency->email)) {
            Notification::route('mail', $agency->email)->notify($notification);
        }
    }
}
