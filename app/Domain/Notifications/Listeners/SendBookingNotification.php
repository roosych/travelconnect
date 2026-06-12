<?php

namespace App\Domain\Notifications\Listeners;

use App\Domain\Bookings\Enums\BookingStatus;
use App\Domain\Bookings\Events\BookingStatusChanged;
use App\Domain\Bookings\Notifications\BookingStatusNotification;
use Illuminate\Support\Facades\Notification;

class SendBookingNotification
{
    /** Statuses the agency is notified about. */
    private const NOTIFIED = [
        BookingStatus::AwaitingPayment,
        BookingStatus::Paid,
        BookingStatus::Completed,
        BookingStatus::Cancelled,
    ];

    public function handle(BookingStatusChanged $event): void
    {
        if (! in_array($event->status, self::NOTIFIED, true)) {
            return;
        }

        $booking = $event->booking;
        $booking->loadMissing('agency.users');

        $agency = $booking->agency;
        if ($agency === null) {
            return;
        }

        $users = $agency->users;

        if ($users->isNotEmpty()) {
            Notification::send($users, new BookingStatusNotification($booking, $event->status));

            return;
        }

        if (! empty($agency->email)) {
            Notification::route('mail', $agency->email)
                ->notify(new BookingStatusNotification($booking, $event->status));
        }
    }
}
