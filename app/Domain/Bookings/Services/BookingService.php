<?php

namespace App\Domain\Bookings\Services;

use App\Domain\Bookings\Enums\BookingStatus;
use App\Domain\Bookings\Events\BookingStatusChanged;
use App\Domain\Bookings\Models\Booking;
use App\Domain\Bookings\Models\BookingItem;
use App\Domain\Proposals\Models\Proposal;
use App\Domain\Proposals\Services\PricedLine;
use App\Domain\Proposals\Services\ProposalPricing;
use App\Domain\Requests\Enums\RequestStatus;
use App\Domain\Users\Models\User;
use App\Exceptions\Domain\BusinessRuleException;
use App\Exceptions\Domain\InvalidStatusTransitionException;
use Illuminate\Support\Carbon;

class BookingService
{
    public function __construct(
        private readonly ProposalPricing $pricing,
    ) {}

    /**
     * Called atomically inside ProposalService::accept().
     * Copies all relevant fields from the proposal and its parent request,
     * and freezes the cost-of-goods snapshot (booking_items + AZN rollups) so
     * margin reporting survives later edits to the source offers/suppliers.
     */
    public function createFromProposal(Proposal $proposal): Booking
    {
        $request = $proposal->request;

        $lines = $this->pricing->lines($proposal);
        $costTotalAzn = round($lines->sum(fn (PricedLine $l) => $l->netAmountAzn), 2);
        $sellTotalAzn = round($lines->sum(fn (PricedLine $l) => $l->sellAmountAzn), 2);

        // Invariant: the frozen sell total must equal the AZN brutto the agency
        // was actually sold. original_total_price holds the AZN amount before FX
        // conversion to the agency currency; for AZN agencies it is null and the
        // total is already in AZN. A mismatch means the pricing logic drifted.
        $soldAzn = (float) ($proposal->original_total_price ?? $proposal->total_price);
        if (abs($sellTotalAzn - $soldAzn) > 0.01) {
            throw new BusinessRuleException(
                "Снапшот себестоимости разошёлся с ценой КП (снапшот: {$sellTotalAzn} AZN, КП: {$soldAzn} AZN)."
            );
        }

        $booking = Booking::create([
            'proposal_id' => $proposal->id,
            'request_id' => $request->id,
            'agency_id' => $request->agency_id,
            'operator_id' => $proposal->operator_id,
            'confirmed_at' => Carbon::now(),
            'travel_date_from' => $request->travel_date_from,
            'travel_date_to' => $request->travel_date_to,
            'pax_count' => $request->pax_count,
            'final_price' => $proposal->total_price,
            'currency' => $proposal->currency,
            'cost_total_azn' => $costTotalAzn,
            'sell_total_azn' => $sellTotalAzn,
            'margin_azn' => round($sellTotalAzn - $costTotalAzn, 2),
            'fx_rate_to_agency' => $proposal->exchange_rate_snapshot !== null
                ? (float) $proposal->exchange_rate_snapshot
                : 1.0,
            'status' => BookingStatus::Confirmed,
            'notes' => null,
        ]);

        foreach ($lines as $line) {
            $booking->items()->create([
                'offer_id' => $line->offerId,
                'supplier_id' => $line->supplierId,
                'supplier_name' => $line->supplierName,
                'service_type' => $line->serviceType,
                'name' => $line->name,
                'description' => $line->description,
                'quantity' => $line->quantity,
                'net_unit_price' => $line->netUnitPrice,
                'net_currency' => $line->netCurrency,
                'net_fx_rate' => $line->netFxRate,
                'net_amount_azn' => $line->netAmountAzn,
                'markup_pct' => $line->markupPct,
                'sell_amount_azn' => $line->sellAmountAzn,
            ]);
        }

        $request->status = RequestStatus::Booked;
        $request->save();

        return $booking;
    }

    public function requestPayment(Booking $booking, User $operator): Booking
    {
        $allowed = [BookingStatus::Confirmed, BookingStatus::Rescheduled];

        if (! in_array($booking->status, $allowed, true)) {
            throw new InvalidStatusTransitionException('Booking', $booking->status->value, 'awaiting_payment');
        }

        $booking->status = BookingStatus::AwaitingPayment;
        $booking->save();

        BookingStatusChanged::dispatch($booking, $booking->status);

        return $booking;
    }

    public function markPaid(Booking $booking, User $operator): Booking
    {
        if ($booking->status !== BookingStatus::AwaitingPayment) {
            throw new InvalidStatusTransitionException('Booking', $booking->status->value, 'paid');
        }

        $booking->status = BookingStatus::Paid;
        $booking->save();

        BookingStatusChanged::dispatch($booking, $booking->status);

        return $booking;
    }

    public function complete(Booking $booking, User $operator, string $notes): Booking
    {
        if ($booking->status !== BookingStatus::InProgress) {
            throw new InvalidStatusTransitionException('Booking', $booking->status->value, 'completed');
        }

        if (empty(trim($notes))) {
            throw new BusinessRuleException('Notes are required to complete a booking.');
        }

        $booking->status = BookingStatus::Completed;
        $booking->notes = $notes;
        $booking->save();

        $request = $booking->request;

        if ($request->status === RequestStatus::Booked) {
            $request->status = RequestStatus::Completed;
            $request->save();
        }

        BookingStatusChanged::dispatch($booking, $booking->status);

        return $booking;
    }

    public function cancel(Booking $booking, User $operator, string $notes): Booking
    {
        $terminal = [BookingStatus::Completed, BookingStatus::Cancelled];

        if (in_array($booking->status, $terminal, true)) {
            throw new InvalidStatusTransitionException('Booking', $booking->status->value, 'cancelled');
        }

        if (empty(trim($notes))) {
            throw new BusinessRuleException('Notes are required to cancel a booking.');
        }

        $booking->status = BookingStatus::Cancelled;
        $booking->notes = $notes;
        $booking->save();

        $request = $booking->request;

        if (in_array($request->status, [RequestStatus::Booked, RequestStatus::Completed], true)) {
            $request->status = RequestStatus::Processing;
            $request->save();
        }

        BookingStatusChanged::dispatch($booking, $booking->status);

        return $booking;
    }
}
