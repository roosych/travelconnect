<?php

namespace App\Domain\Bookings\Events;

use App\Domain\Bookings\Enums\BookingStatus;
use App\Domain\Bookings\Models\Booking;
use Illuminate\Foundation\Events\Dispatchable;

class BookingStatusChanged
{
    use Dispatchable;

    public function __construct(
        public readonly Booking $booking,
        public readonly BookingStatus $status,
    ) {}
}
