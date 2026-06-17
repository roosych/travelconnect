<?php

namespace App\Domain\Bookings\Listeners;

use App\Domain\Bookings\Enums\BookingStatus;
use App\Domain\Bookings\Events\BookingStatusChanged;
use App\Domain\Bookings\Models\Booking;
use App\Domain\Payments\Enums\PaymentDirection;
use App\Domain\Payments\Events\PaymentConfirmed;
use App\Domain\Payments\Services\PaymentService;

/**
 * Когда подтверждён ВХОДЯЩИЙ платёж и входящий леджер брони полностью закрыт —
 * автоматически переводим бронь в paid. Слабая связь: домен Payments шлёт событие,
 * домен Bookings реагирует.
 */
class AdvanceBookingOnPaymentConfirmed
{
    public function __construct(private readonly PaymentService $payments) {}

    public function handle(PaymentConfirmed $event): void
    {
        $payment = $event->payment;

        if ($payment->direction !== PaymentDirection::Incoming) {
            return;
        }

        $booking = $payment->payable;
        if (! $booking instanceof Booking) {
            return;
        }

        if (! in_array($booking->status, [BookingStatus::Confirmed, BookingStatus::AwaitingPayment], true)) {
            return;
        }

        if ($this->payments->incomingSettled($booking)) {
            $booking->status = BookingStatus::Paid;
            $booking->save();

            BookingStatusChanged::dispatch($booking, $booking->status);
        }
    }
}
