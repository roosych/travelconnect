<?php

namespace App\Domain\Offers\Http\Resources;

use App\Domain\Suppliers\Http\Resources\SupplierResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfferResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isSupplier = $request->user()?->isSupplier() ?? false;

        // Operators/agencies work in AZN: expose the AZN snapshot under unit_price
        // and keep the supplier's original amount/currency as reference.
        // Suppliers see their own currency.
        $aznUnit = (float) ($this->unit_price_azn ?? $this->unit_price);

        return [
            'id' => $this->id,
            'rfq_id' => $this->rfq_id,
            'rfq_title' => $this->whenLoaded('rfq', fn () => $this->rfq->title ?? ucfirst($this->rfq->service_type ?? '')),
            'rfq_service_type' => $this->whenLoaded('rfq', fn () => $this->rfq->service_type),
            'rfq' => $this->whenLoaded('rfq', fn () => [
                'id' => $this->rfq->id,
                'title' => $this->rfq->title,
                'service_type' => $this->rfq->service_type,
                'deadline_at' => $this->rfq->deadline_at?->toIso8601String(),
                'country_code' => $this->rfq->country_code,
                'country_flag' => $this->rfq->country_code ? asset('flags/' . strtolower($this->rfq->country_code) . '.svg') : null,
                'country_name' => $this->rfq->relationLoaded('country') ? $this->rfq->country?->name : null,
                'request' => $this->rfq->relationLoaded('request') && $this->rfq->request ? [
                    'id' => $this->rfq->request->id,
                    'title' => $this->rfq->request->title,
                    'destination' => $this->rfq->request->destination,
                    'travel_date_from' => $this->rfq->request->travel_date_from?->toDateString(),
                    'travel_date_to' => $this->rfq->request->travel_date_to?->toDateString(),
                    'pax_count' => $this->rfq->request->pax_count,
                    'status' => $this->rfq->request->status->value,
                    'status_label' => $this->rfq->request->status->operatorLabel(),
                    'status_badge_class' => $this->rfq->request->status->operatorBadgeClass(),
                    'agency' => $this->rfq->request->relationLoaded('agency') ? [
                        'id' => $this->rfq->request->agency?->id,
                        'name' => $this->rfq->request->agency?->name,
                        'company_name' => $this->rfq->request->agency?->company_name,
                    ] : null,
                ] : null,
            ]),
            'supplier' => new SupplierResource($this->whenLoaded('supplier')),
            'is_partial' => $this->is_partial,
            'covered_services' => $this->covered_services,
            'uncovered_services' => $this->uncovered_services,
            'unit_price' => $isSupplier ? (float) $this->unit_price : $aznUnit,
            'currency' => $isSupplier ? $this->currency : 'AZN',
            'supplier_unit_price' => (float) $this->unit_price,
            'supplier_currency' => $this->currency,
            'unit_price_azn' => $aznUnit,
            'valid_until' => $this->valid_until?->toIso8601String(),
            'notes' => $this->notes,
            'status' => $this->status->value,
            'status_label' => $isSupplier ? $this->status->supplierLabel() : $this->status->operatorLabel(),
            'status_badge_class' => $isSupplier ? $this->status->supplierBadgeClass() : $this->status->operatorBadgeClass(),
            'is_expired' => $this->isExpired(),
            'operator_notes' => $this->whenPivotLoaded('proposal_offer', fn () => $this->pivot->operator_notes),
            'markup_pct' => $this->whenPivotLoaded('proposal_offer', fn () => $this->pivot->markup_pct),
            'selected_item_types' => $this->whenPivotLoaded('proposal_offer', fn () => $this->pivot->selected_item_types
                    ? json_decode($this->pivot->selected_item_types, true)
                    : null
            ),
            'item_markups' => $this->whenPivotLoaded('proposal_offer', fn () => $this->pivot->item_markups
                    ? json_decode($this->pivot->item_markups, true)
                    : null
            ),
            'price_with_markup' => $this->whenPivotLoaded('proposal_offer', function () {
                $selectedTypes = $this->pivot->selected_item_types
                    ? json_decode($this->pivot->selected_item_types, true)
                    : null;
                $itemMarkups = $this->pivot->item_markups
                    ? json_decode($this->pivot->item_markups, true)
                    : null;
                $markupPct = (float) ($this->pivot->markup_pct ?? 0);

                if ($this->relationLoaded('items') && $this->items->isNotEmpty()) {
                    $items = $selectedTypes
                        ? $this->items->filter(fn ($item) => in_array($item->type, $selectedTypes, true))
                        : $this->items;

                    return $items->sum(function ($item) use ($itemMarkups, $markupPct) {
                        $type = $item->type;
                        $pct = $itemMarkups[$type] ?? $markupPct;
                        $base = $item->unit_price_azn ?? $item->unit_price;

                        return $base * (1 + $pct / 100);
                    });
                }

                return ($this->unit_price_azn ?? $this->unit_price) * (1 + ($markupPct / 100));
            }),
            'items' => OfferItemResource::collection($this->whenLoaded('items')),
            'attachments' => $this->whenLoaded('attachments', fn () => $this->attachments->map(fn ($a) => [
                'id' => $a->id,
                'filename' => $a->filename,
                'mime_type' => $a->mime_type,
                'size' => $a->size,
                'human_size' => $a->humanSize(),
                'url' => $a->url(),
                'uploader' => $a->uploader ? ['id' => $a->uploader->id, 'name' => $a->uploader->name] : null,
                'created_at' => $a->created_at->toDateTimeString(),
            ])),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
