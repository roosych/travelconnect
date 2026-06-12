<?php

namespace App\Domain\Proposals\Services;

use App\Domain\Bookings\Models\Booking;
use App\Domain\Bookings\Services\BookingService;
use App\Domain\Offers\Enums\OfferStatus;
use App\Domain\Offers\Events\OfferAccepted;
use App\Domain\Offers\Models\Offer;
use App\Domain\Proposals\Enums\ProposalStatus;
use App\Domain\Proposals\Events\ProposalDecided;
use App\Domain\Proposals\Events\ProposalSent;
use App\Domain\Proposals\Models\Proposal;
use App\Domain\Requests\Enums\RequestStatus;
use App\Domain\Requests\Models\TravelRequest;
use App\Domain\Services\ServiceCatalog;
use App\Domain\Settings\Services\CurrencyConverter;
use App\Domain\Users\Models\User;
use App\Exceptions\Domain\BusinessRuleException;
use App\Exceptions\Domain\InvalidStatusTransitionException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ProposalService
{
    public function __construct(
        private readonly BookingService $bookingService,
        private readonly CurrencyConverter $currencyConverter,
        private readonly ProposalPricing $pricing,
    ) {}

    public function createDraft(array $data, TravelRequest $request, User $operator): Proposal
    {
        if ($request->status !== RequestStatus::Processing) {
            throw new BusinessRuleException(
                "A proposal can only be created for a request in 'processing' status. Current: {$request->status->value}"
            );
        }

        return Proposal::create([
            'request_id' => $request->id,
            'operator_id' => $operator->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? '',
            'total_price' => $data['total_price'] ?? 0,
            'currency' => isset($data['currency']) ? strtoupper($data['currency']) : '',
            // «Действительно до» вводится в поясе оператора → переводим в UTC.
            'valid_until' => isset($data['valid_until']) && $data['valid_until']
                ? Carbon::parse($data['valid_until'], $operator->effectiveTimezone())->utc()
                : Carbon::now()->addDays(30),
            'status' => ProposalStatus::Draft,
        ]);
    }

    public function addOffer(
        Proposal $proposal,
        Offer $offer,
        ?string $operatorNotes = null,
        float $markupPct = 0,
        ?array $selectedItemTypes = null,
        ?array $itemMarkups = null,
    ): void {
        if ($proposal->status !== ProposalStatus::Draft) {
            throw new BusinessRuleException('Offers can only be added to a proposal in draft status.');
        }

        $proposal->loadMissing('request');
        if ($proposal->request->status === RequestStatus::Booked) {
            throw new BusinessRuleException('Заявка уже забронирована. Добавление предложений невозможно.');
        }

        if ($offer->status !== OfferStatus::Selected) {
            throw new BusinessRuleException(
                "Only offers in 'selected' status can be added to a proposal. Offer status: {$offer->status->value}"
            );
        }

        // If explicit item types are provided and offer is already attached — merge with existing selection.
        // If null is sent (add all) — let it override any partial selection (selected_item_types stays null = all items).
        if ($selectedItemTypes !== null) {
            $existingPivot = $proposal->offers()
                ->where('offers.id', $offer->id)
                ->withPivot('selected_item_types')
                ->first();
            if ($existingPivot && $existingPivot->pivot->selected_item_types) {
                $existingTypes = json_decode($existingPivot->pivot->selected_item_types, true) ?? [];
                $selectedItemTypes = array_values(array_unique(array_merge($existingTypes, $selectedItemTypes)));
            }
        }

        // Duplicate service type check — excludes the current offer so markup-only updates (Apply) are allowed.
        $newTypes = $selectedItemTypes
            ?? ($offer->items->isNotEmpty()
                ? $offer->items->map(fn ($i) => $i->type)->toArray()
                : array_filter([$offer->rfq?->service_type]));

        $otherOffers = $proposal->offers()
            ->where('offers.id', '!=', $offer->id)
            ->withPivot('selected_item_types')
            ->with('items')
            ->get();

        $coveredTypes = [];
        foreach ($otherOffers as $existing) {
            $sel = $existing->pivot->selected_item_types
                ? json_decode($existing->pivot->selected_item_types, true)
                : null;
            if ($sel) {
                $coveredTypes = array_merge($coveredTypes, $sel);
            } elseif ($existing->items->isNotEmpty()) {
                foreach ($existing->items as $item) {
                    $coveredTypes[] = $item->type;
                }
            } elseif ($existing->rfq?->service_type) {
                $coveredTypes[] = $existing->rfq->service_type;
            }
        }

        $duplicates = array_intersect($newTypes, $coveredTypes);
        if (! empty($duplicates)) {
            $catalog = app(ServiceCatalog::class);
            $labels = implode(', ', array_map(
                fn ($v) => $catalog->typeLabel($v),
                array_values($duplicates)
            ));
            throw new BusinessRuleException("Услуга уже включена в КП: {$labels}.");
        }

        // Idempotent: if already attached, update notes, markup and selected items
        $proposal->offers()->syncWithoutDetaching([
            $offer->id => [
                'operator_notes' => $operatorNotes ?? '',
                'markup_pct' => $markupPct,
                'selected_item_types' => $selectedItemTypes ? json_encode($selectedItemTypes) : null,
                'item_markups' => $itemMarkups ? json_encode($itemMarkups) : null,
            ],
        ]);

        $this->recalculateTotal($proposal);
    }

    public function removeOffer(Proposal $proposal, Offer $offer): void
    {
        if ($proposal->status !== ProposalStatus::Draft) {
            throw new BusinessRuleException('Offers can only be removed from a proposal in draft status.');
        }

        $proposal->offers()->detach($offer->id);
        $this->recalculateTotal($proposal);
    }

    public function delete(Proposal $proposal): void
    {
        if (in_array($proposal->status, [ProposalStatus::Sent, ProposalStatus::Accepted], true)) {
            throw new BusinessRuleException(
                "Cannot delete a proposal in '{$proposal->status->value}' status."
            );
        }

        // Reset attached offers back to 'reviewed' so they can be reused
        foreach ($proposal->offers as $offer) {
            if ($offer->status === OfferStatus::Selected) {
                $offer->status = OfferStatus::Reviewed;
                $offer->save();
            }
        }

        $proposal->offers()->detach();
        $proposal->delete();
    }

    public function send(Proposal $proposal, User $operator): Proposal
    {
        if ($proposal->status !== ProposalStatus::Draft) {
            throw new InvalidStatusTransitionException('Proposal', $proposal->status->value, 'sent');
        }

        $proposal->loadMissing('request.agency');
        if ($proposal->request->status === RequestStatus::Booked) {
            throw new BusinessRuleException('Заявка уже забронирована. Отправка предложения невозможна.');
        }

        $this->assertSendConditions($proposal);

        // Snapshot: convert total_price to agency currency at current exchange rate.
        // AZN is the system's base operating currency regardless of offer.currency labels.
        $agencyCurrency = $proposal->request->agency?->currency_code;
        $workingCurrency = 'AZN';

        if ($agencyCurrency && $agencyCurrency !== $workingCurrency) {
            $rate = $this->currencyConverter->getRate($agencyCurrency);

            if ($rate === null || $rate <= 0) {
                throw new BusinessRuleException(
                    "Курс валюты {$agencyCurrency} не задан. Настройте курс в разделе Настройки → Валюты."
                );
            }

            $originalPrice = (float) $proposal->total_price;
            $convertedPrice = $this->currencyConverter->convert($originalPrice, $workingCurrency, $agencyCurrency);
            $proposal->original_total_price = $originalPrice;
            $proposal->original_currency = $workingCurrency;
            $proposal->exchange_rate_snapshot = $rate;
            $proposal->total_price = $convertedPrice;
            $proposal->currency = $agencyCurrency;
        }

        $proposal->status = ProposalStatus::Sent;
        $proposal->save();

        ProposalSent::dispatch($proposal);

        return $proposal;
    }

    /**
     * Agency accepts proposal — atomically transitions proposal to accepted and creates a booking.
     * All other sent proposals for the same request are auto-rejected.
     */
    public function accept(Proposal $proposal, User $agency): Proposal
    {
        if ($proposal->status !== ProposalStatus::Sent) {
            throw new InvalidStatusTransitionException('Proposal', $proposal->status->value, 'accepted');
        }

        if ($proposal->isExpired()) {
            throw new BusinessRuleException('Cannot accept an expired proposal.');
        }

        $proposal->loadMissing('request');

        if ($proposal->request->status === RequestStatus::Booked) {
            throw new BusinessRuleException('Заявка уже забронирована по другому предложению.');
        }

        $proposal = DB::transaction(function () use ($proposal) {
            $proposal->status = ProposalStatus::Accepted;
            $proposal->accepted_at = Carbon::now();
            $proposal->save();

            Proposal::where('request_id', $proposal->request_id)
                ->where('id', '!=', $proposal->id)
                ->where('status', ProposalStatus::Sent->value)
                ->update(['status' => ProposalStatus::Rejected->value]);

            $this->bookingService->createFromProposal($proposal);

            return $proposal;
        });

        // Notify suppliers whose offers made it into the accepted proposal.
        foreach ($proposal->offers()->with('supplier.users', 'rfq')->get() as $offer) {
            OfferAccepted::dispatch($offer);
        }

        // Notify the operator who built the proposal that the agency accepted it.
        ProposalDecided::dispatch($proposal, true);

        return $proposal;
    }

    public function reject(Proposal $proposal, User $agency): Proposal
    {
        if ($proposal->status !== ProposalStatus::Sent) {
            throw new InvalidStatusTransitionException('Proposal', $proposal->status->value, 'rejected');
        }

        $proposal->status = ProposalStatus::Rejected;
        $proposal->save();

        ProposalDecided::dispatch($proposal, false);

        return $proposal;
    }

    public function cancel(Proposal $proposal, User $operator): Proposal
    {
        $allowed = [ProposalStatus::Draft, ProposalStatus::Sent];

        if (! in_array($proposal->status, $allowed, true)) {
            throw new InvalidStatusTransitionException('Proposal', $proposal->status->value, 'cancelled');
        }

        $proposal->loadMissing('offers');
        foreach ($proposal->offers as $offer) {
            if ($offer->status === OfferStatus::Selected) {
                $offer->status = OfferStatus::Reviewed;
                $offer->save();
            }
        }

        $proposal->status = ProposalStatus::Cancelled;
        $proposal->save();

        return $proposal;
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function recalculateTotal(Proposal $proposal): void
    {
        // ProposalPricing is the single source of truth for line pricing/markup,
        // shared with the booking cost snapshot so the two can never diverge.
        $proposal->update([
            'total_price' => $this->pricing->total($proposal),
            'currency'    => 'AZN', // AZN is the system's base operating currency
        ]);
    }

    private function assertSendConditions(Proposal $proposal): void
    {
        // PS-1: title required
        if (empty($proposal->title)) {
            throw new BusinessRuleException('Proposal title is required before sending.');
        }

        // PS-2: total_price > 0, currency set
        if ($proposal->total_price <= 0) {
            throw new BusinessRuleException('total_price must be greater than 0 before sending.');
        }

        if (empty($proposal->currency)) {
            throw new BusinessRuleException('currency is required before sending.');
        }

        // PS-3: valid_until is a future moment
        if (empty($proposal->valid_until) || $proposal->valid_until->lt(Carbon::now())) {
            throw new BusinessRuleException('valid_until must be a future date before sending.');
        }

        // PS-4: at least one offer in proposal_offer
        // withPivot is declared on the relationship; just call get() to load pivot data
        $offers = $proposal->offers()->withPivot('selected_item_types')->get();

        if ($offers->isEmpty()) {
            throw new BusinessRuleException('At least one offer must be attached before sending a proposal.');
        }

        // PS-5: all linked offers are selected and not expired
        $offers->load('rfq', 'items');
        foreach ($offers as $offer) {
            if ($offer->status !== OfferStatus::Selected) {
                throw new BusinessRuleException(
                    "Offer #{$offer->id} is not in 'selected' status (current: {$offer->status->value})."
                );
            }

            if ($offer->isExpired()) {
                throw new BusinessRuleException("Offer #{$offer->id} has expired and cannot be part of a sent proposal.");
            }
        }

        // PS-6: all services_needed of the request must be covered by at least one offer
        $request = $proposal->request;
        if ($request && ! empty($request->services_needed)) {
            $coveredTypes = [];
            foreach ($offers as $o) {
                $selectedTypes = $o->pivot->selected_item_types
                    ? json_decode($o->pivot->selected_item_types, true)
                    : null;
                if ($selectedTypes) {
                    foreach ($selectedTypes as $t) {
                        $coveredTypes[] = $t;
                    }
                } elseif ($o->items->isNotEmpty()) {
                    foreach ($o->items as $item) {
                        $coveredTypes[] = $item->type;
                    }
                } elseif ($o->rfq?->service_type) {
                    $coveredTypes[] = $o->rfq->service_type;
                }
            }
            $coveredTypes = array_unique($coveredTypes);

            $missing = array_diff($request->services_needed, $coveredTypes);
            if (! empty($missing)) {
                $catalog = app(ServiceCatalog::class);
                $labels = implode(', ', array_map(
                    fn ($v) => $catalog->typeLabel($v),
                    $missing
                ));
                throw new BusinessRuleException(
                    "КП не покрывает все необходимые услуги заявки. Не покрыто: {$labels}."
                );
            }
        }
    }
}
