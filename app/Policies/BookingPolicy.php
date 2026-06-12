<?php

namespace App\Policies;

use App\Domain\Bookings\Models\Booking;
use App\Domain\Users\Models\User;
use App\Policies\Concerns\BypassesForOperator;

class BookingPolicy
{
    use BypassesForOperator;

    public function view(User $user, Booking $booking): bool
    {
        if ($user->isAgency()) {
            return $user->agencies()->pluck('agencies.id')->contains($booking->agency_id);
        }

        return false;
    }
}
