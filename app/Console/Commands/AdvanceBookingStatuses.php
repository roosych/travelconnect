<?php

namespace App\Console\Commands;

use App\Domain\Bookings\Enums\BookingStatus;
use App\Domain\Bookings\Models\Booking;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class AdvanceBookingStatuses extends Command
{
    protected $signature = 'bookings:advance-status';

    protected $description = 'Advance bookings to in_progress when the travel start date is reached';

    public function handle(): int
    {
        $today = Carbon::today();

        // confirmed / paid → in_progress when travel has started.
        // Completion stays a manual operator action (with closing notes).
        $started = Booking::whereIn('status', [
            BookingStatus::Confirmed->value,
            BookingStatus::Paid->value,
        ])
            ->whereNotNull('travel_date_from')
            ->where('travel_date_from', '<=', $today)
            ->update(['status' => BookingStatus::InProgress->value]);

        $this->info("Started: {$started} booking(s) → in_progress.");

        return self::SUCCESS;
    }
}
