<?php

namespace App\Domain\Offers\Services;

use App\Domain\Offers\Enums\OfferStatus;
use App\Domain\Offers\Events\OfferRejected;
use App\Domain\Offers\Events\OfferSubmitted;
use App\Domain\Offers\Models\Offer;
use App\Domain\Proposals\Enums\ProposalStatus;
use App\Domain\Proposals\Models\Proposal;
use App\Domain\RFQs\Enums\RfqStatus;
use App\Domain\RFQs\Models\Rfq;
use App\Domain\Suppliers\Enums\PriceUnit;
use App\Domain\Suppliers\Models\SupplierIncident;
use App\Domain\Users\Models\User;
use App\Exceptions\Domain\BusinessRuleException;
use App\Exceptions\Domain\InvalidStatusTransitionException;
use App\Services\CbarService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class OfferService
{
    public function __construct(
        private readonly CbarService $cbar,
    ) {}

    /**
     * @param  bool  $operatorEntered  True when an operator enters the offer directly (not via supplier portal).
     *                                 In that case the offer skips 'received' and goes straight to 'reviewed'.
     */
    public function recordOffer(array $data, Rfq $rfq, int $supplierId, ?User $operator = null, bool $operatorEntered = false): Offer
    {
        $allowedRfqStatuses = [RfqStatus::Sent, RfqStatus::Awaiting];

        if (! in_array($rfq->status, $allowedRfqStatuses, true)) {
            throw new BusinessRuleException(
                "Предложения принимаются только по запросам в статусе «отправлен» или «ожидание». Текущий: {$rfq->status->value}"
            );
        }

        $this->assertOfferCreateData($data);

        $currency = strtoupper($data['currency']);
        $exchangeRate = $this->cbar->getRateToAzn($currency);
        $priceAzn = $exchangeRate !== null
            ? round($data['unit_price'] * $exchangeRate, 2)
            : null;

        $offer = DB::transaction(function () use ($data, $rfq, $supplierId, $operatorEntered, $currency, $exchangeRate, $priceAzn) {
            $offer = Offer::create([
                'rfq_id' => $rfq->id,
                'supplier_id' => $supplierId,
                'is_partial' => $data['is_partial'] ?? false,
                'covered_services' => $data['covered_services'],
                'uncovered_services' => $data['uncovered_services'] ?? null,
                'unit_price' => $data['unit_price'],
                'currency' => $currency,
                'exchange_rate' => $exchangeRate,
                'unit_price_azn' => $priceAzn,
                'valid_until' => $data['valid_until'],
                'notes' => $data['notes'] ?? null,
                'status' => $operatorEntered ? OfferStatus::Reviewed : OfferStatus::Received,
            ]);

            // Create line items when provided (e.g. from supplier compose page with catalog selection)
            foreach ($data['items'] ?? [] as $item) {
                $itemCurrency = strtoupper($item['currency'] ?? $currency);
                $itemExRate = $this->cbar->getRateToAzn($itemCurrency);
                $offer->items()->create([
                    'supplier_service_id' => $item['supplier_service_id'] ?? null,
                    'type' => $item['type'],
                    'name' => $item['name'] ?? null,
                    'quantity' => 1,
                    'unit_price' => $item['unit_price'],
                    'currency' => $itemCurrency,
                    'unit_price_azn' => $itemExRate ? round($item['unit_price'] * $itemExRate, 2) : null,
                    'exchange_rate' => $itemExRate,
                    'price_unit' => PriceUnit::Fixed->value,
                ]);
            }

            // Auto-advance RFQ from sent → awaiting on first offer
            if ($rfq->status === RfqStatus::Sent) {
                $rfq->status = RfqStatus::Awaiting;
                $rfq->save();
            }

            return $offer;
        });

        // Notify the RFQ's operator only when the supplier submitted it themselves
        // (an operator entering an offer on the supplier's behalf doesn't need a ping).
        if (! $operatorEntered) {
            OfferSubmitted::dispatch($offer);
        }

        return $offer;
    }

    /**
     * Update a still-pending supplier offer in place (price/notes + its single line
     * item). Used by the supplier token portal «edit» flow. Re-snapshots the AZN
     * equivalent at the current rate, like recordOffer.
     */
    public function updateOffer(Offer $offer, array $data): Offer
    {
        if (! in_array($offer->status, [OfferStatus::Received, OfferStatus::Reviewed], true)) {
            throw new BusinessRuleException(
                "Предложение можно изменить только пока оно на рассмотрении. Текущий статус: {$offer->status->value}"
            );
        }

        $currency     = strtoupper($offer->currency);
        $exchangeRate = $this->cbar->getRateToAzn($currency);
        $priceAzn     = $exchangeRate !== null ? round($data['unit_price'] * $exchangeRate, 2) : null;

        return DB::transaction(function () use ($offer, $data, $priceAzn, $exchangeRate) {
            $offer->update([
                'unit_price'     => $data['unit_price'],
                'unit_price_azn' => $priceAzn,
                'exchange_rate'  => $exchangeRate,
                'notes'          => $data['notes'] ?? null,
            ]);

            $item = $offer->items()->first();
            if ($item !== null) {
                $item->update([
                    'name'                => $data['name'] ?? $item->type,
                    'unit_price'          => $data['unit_price'],
                    'unit_price_azn'      => $priceAzn,
                    'exchange_rate'       => $exchangeRate,
                    'supplier_service_id' => $data['supplier_service_id'] ?? null,
                ]);
            }

            return $offer->fresh(['items']);
        });
    }

    /**
     * Operator selects an offer and attaches it to a proposal.
     */
    public function select(Offer $offer, Proposal $proposal): Offer
    {
        if ($offer->status === OfferStatus::Selected) {
            return $offer; // idempotent
        }

        if (! in_array($offer->status, [OfferStatus::Received, OfferStatus::Reviewed], true)) {
            throw new InvalidStatusTransitionException('Offer', $offer->status->value, 'selected');
        }

        if ($offer->isExpired()) {
            throw new BusinessRuleException('Cannot select an expired offer.');
        }

        if ($offer->rfq->status === RfqStatus::Cancelled) {
            throw new BusinessRuleException('Нельзя выбрать предложение из отменённого запроса.');
        }

        $offer->status = OfferStatus::Selected;
        $offer->save();

        return $offer;
    }

    /**
     * Removes an offer from a proposal — transitions selected → reviewed.
     * Proposal must still be in draft.
     */
    public function removeFromProposal(Offer $offer, Proposal $proposal): Offer
    {
        if ($offer->status !== OfferStatus::Selected) {
            throw new InvalidStatusTransitionException('Offer', $offer->status->value, 'reviewed');
        }

        if ($proposal->status !== ProposalStatus::Draft) {
            throw new BusinessRuleException(
                'Can only remove an offer from a proposal while the proposal is in draft status.'
            );
        }

        $offer->status = OfferStatus::Reviewed;
        $offer->save();

        return $offer;
    }

    public function reject(Offer $offer): Offer
    {
        $allowedFrom = [OfferStatus::Received, OfferStatus::Reviewed];

        if (! in_array($offer->status, $allowedFrom, true)) {
            throw new InvalidStatusTransitionException('Offer', $offer->status->value, 'rejected');
        }

        $offer->status = OfferStatus::Rejected;
        $offer->save();

        OfferRejected::dispatch($offer);

        return $offer;
    }

    public function markWithdrawn(Offer $offer): Offer
    {
        $allowedFrom = [OfferStatus::Received, OfferStatus::Reviewed, OfferStatus::Selected];

        if (! in_array($offer->status, $allowedFrom, true)) {
            throw new InvalidStatusTransitionException('Offer', $offer->status->value, 'withdrawn');
        }

        $blockedProposalStatuses = [
            ProposalStatus::Sent->value,
            ProposalStatus::Accepted->value,
        ];

        $inActiveProposal = $offer->proposals()
            ->whereIn('status', $blockedProposalStatuses)
            ->exists();

        if ($inActiveProposal) {
            throw new BusinessRuleException(
                'Нельзя отозвать предложение: оно включено в отправленное или принятое коммерческое предложение.'
            );
        }

        $fromStatus = $offer->status;

        $offer->status = OfferStatus::Withdrawn;
        $offer->save();

        SupplierIncident::recordOfferWithdrawn(
            supplierId: $offer->supplier_id,
            offerId: $offer->id,
            fromStatus: $fromStatus,
            rfqCode: $offer->rfq?->public_code,
        );

        return $offer;
    }

    /**
     * On-read expiry check. If valid_until < today and offer is not already in a terminal state,
     * advance it to expired and persist.
     */
    public function checkExpiry(Offer $offer): Offer
    {
        // Only pending offers expire. A selected (won) offer is terminal — once the
        // operator picked it, valid_until no longer matters and it must NOT auto-expire.
        // (Matches the offers:expire command, which only touches received/reviewed.)
        $expirable = [OfferStatus::Received, OfferStatus::Reviewed];

        if (! in_array($offer->status, $expirable, true)) {
            return $offer;
        }

        if ($offer->valid_until !== null && $offer->valid_until->lt(Carbon::today())) {
            $offer->status = OfferStatus::Expired;
            $offer->save();
        }

        return $offer;
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function assertOfferCreateData(array $data): void
    {
        if (empty($data['covered_services'])) {
            throw new BusinessRuleException('covered_services must not be empty.');
        }

        if (! isset($data['unit_price']) || $data['unit_price'] <= 0) {
            throw new BusinessRuleException('unit_price must be greater than 0.');
        }

        if (empty($data['currency'])) {
            throw new BusinessRuleException('currency is required.');
        }

        if (empty($data['valid_until'])) {
            throw new BusinessRuleException('valid_until is required.');
        }

        if (Carbon::parse($data['valid_until'])->lt(Carbon::today())) {
            throw new BusinessRuleException('valid_until must be a future date.');
        }

        $isPartial = $data['is_partial'] ?? false;

        if ($isPartial && empty($data['uncovered_services'])) {
            throw new BusinessRuleException('uncovered_services must not be empty when is_partial is true.');
        }

        if (! $isPartial && ! empty($data['uncovered_services'])) {
            throw new BusinessRuleException('uncovered_services must be empty when is_partial is false.');
        }
    }
}
