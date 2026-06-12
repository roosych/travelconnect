<?php

namespace App\Domain\Proposals\Services;

use App\Domain\Offers\Models\Offer;
use App\Domain\Proposals\Models\Proposal;
use App\Domain\Services\ServiceCatalog;
use Illuminate\Support\Collection;

/**
 * Single source of truth for proposal pricing.
 *
 * Resolves a proposal's attached offers (with their per-offer / per-item markups
 * and partial selection) into a flat list of priced lines in AZN. Both the live
 * proposal total (ProposalService::recalculateTotal) and the immutable booking
 * cost snapshot (BookingService::createFromProposal) are derived from this, so
 * the price the agency was sold and the cost frozen on the booking always match.
 *
 * Business rule: suppliers quote a price for the whole request, so quantity is
 * always 1 and net_amount_azn equals the (per-unit) AZN snapshot of the line.
 */
class ProposalPricing
{
    /**
     * @return Collection<int, PricedLine>
     */
    public function lines(Proposal $proposal): Collection
    {
        $offers = $proposal->offers()
            ->withPivot('markup_pct', 'selected_item_types', 'item_markups')
            ->with(['items', 'supplier', 'rfq'])
            ->get();

        $lines = collect();

        foreach ($offers as $offer) {
            $selectedTypes = $offer->pivot->selected_item_types
                ? json_decode($offer->pivot->selected_item_types, true)
                : null;
            $itemMarkups = $offer->pivot->item_markups
                ? json_decode($offer->pivot->item_markups, true)
                : null;
            $markupPct = (float) ($offer->pivot->markup_pct ?? 0);
            $supplierName = $offer->supplier?->name ?? '—';

            if ($offer->items->isNotEmpty()) {
                $items = $selectedTypes
                    ? $offer->items->filter(fn ($item) => in_array($item->type, $selectedTypes, true))
                    : $offer->items;

                foreach ($items as $item) {
                    $type = $item->type;
                    $pct = (float) ($itemMarkups[$type] ?? $markupPct);
                    // AZN snapshot is the working amount (offers may be in supplier currency).
                    $netAzn = round((float) ($item->unit_price_azn ?? $item->unit_price), 2);

                    $lines->push(new PricedLine(
                        offerId: $offer->id,
                        supplierId: $offer->supplier_id,
                        supplierName: $supplierName,
                        serviceType: $type,
                        name: $item->name ?? '',
                        description: $item->description,
                        quantity: 1,
                        netUnitPrice: (float) $item->unit_price,
                        netCurrency: $item->currency ?? $offer->currency,
                        netFxRate: $item->exchange_rate !== null ? (float) $item->exchange_rate : null,
                        netAmountAzn: $netAzn,
                        markupPct: $pct,
                        sellAmountAzn: round($netAzn * (1 + $pct / 100), 2),
                    ));
                }

                continue;
            }

            // Legacy offer without line items: one line priced off the offer total.
            $type = $offer->rfq?->service_type;
            $netAzn = round((float) ($offer->unit_price_azn ?? $offer->unit_price), 2);

            $lines->push(new PricedLine(
                offerId: $offer->id,
                supplierId: $offer->supplier_id,
                supplierName: $supplierName,
                serviceType: $type,
                name: $type ? app(ServiceCatalog::class)->typeLabel($type) : 'Услуга',
                description: $offer->notes,
                quantity: 1,
                netUnitPrice: (float) $offer->unit_price,
                netCurrency: $offer->currency,
                netFxRate: $offer->exchange_rate !== null ? (float) $offer->exchange_rate : null,
                netAmountAzn: $netAzn,
                markupPct: $markupPct,
                sellAmountAzn: round($netAzn * (1 + $markupPct / 100), 2),
            ));
        }

        return $lines;
    }

    /**
     * Proposal sell total in AZN — sum of per-line sell amounts.
     */
    public function total(Proposal $proposal): float
    {
        return round(
            $this->lines($proposal)->sum(fn (PricedLine $line) => $line->sellAmountAzn),
            2
        );
    }
}
