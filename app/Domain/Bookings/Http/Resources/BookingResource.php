<?php

namespace App\Domain\Bookings\Http\Resources;

use App\Domain\Agencies\Http\Resources\AgencyResource;
use App\Domain\Users\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isAgency = $request->user()?->isAgency() ?? false;

        return [
            'id' => $this->public_code,
            'confirmed_at' => $this->confirmed_at?->toIso8601String(),
            'travel_date_from' => $this->travel_date_from?->toDateString(),
            'travel_date_to' => $this->travel_date_to?->toDateString(),
            'pax_count' => $this->pax_count,
            'final_price' => $this->final_price,
            'currency' => $this->currency,
            // Operator-facing AZN amount (from the proposal snapshot) + agency reference.
            'final_price_azn' => (float) (($this->relationLoaded('proposal') && $this->proposal) ? ($this->proposal->original_total_price ?? $this->final_price) : $this->final_price),
            'agency_final_price' => (float) $this->final_price,
            'agency_currency' => $this->currency,
            'status' => $this->status->value,
            'status_label' => $isAgency ? $this->status->agencyLabel() : $this->status->operatorLabel(),
            'status_badge_class' => $isAgency ? $this->status->agencyBadgeClass() : $this->status->operatorBadgeClass(),
            'notes' => $this->notes,
            // Cost-of-goods snapshot (operator only): frozen at proposal acceptance.
            'cost_total_azn' => $isAgency ? null : (float) $this->cost_total_azn,
            'sell_total_azn' => $isAgency ? null : (float) $this->sell_total_azn,
            'margin_azn' => $isAgency ? null : (float) $this->margin_azn,
            'margin_pct' => ($isAgency || ! $this->cost_total_azn) ? null
                : round(((float) $this->margin_azn) / ((float) $this->cost_total_azn) * 100, 1),
            'items' => $isAgency ? null : $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'id' => $item->id,
                'service_type' => $item->service_type,
                'name' => $item->name,
                'supplier_name' => $item->supplier_name,
                'quantity' => $item->quantity,
                'net_amount_azn' => (float) $item->net_amount_azn,
                'markup_pct' => (float) $item->markup_pct,
                'sell_amount_azn' => (float) $item->sell_amount_azn,
                'margin_azn' => (float) $item->sell_amount_azn - (float) $item->net_amount_azn,
            ])),
            'agency' => new AgencyResource($this->whenLoaded('agency')),
            'operator' => new UserResource($this->whenLoaded('operator')),
            'proposal' => $this->whenLoaded('proposal', fn () => [
                'id' => $this->proposal->public_code,
                'title' => $this->proposal->title,
                'request' => $this->proposal->relationLoaded('request') && $this->proposal->request ? [
                    'id' => $this->proposal->request->public_code,
                    'title' => $this->proposal->request->title,
                    'destination' => $this->proposal->request->destination,
                    'services_needed' => $this->proposal->request->services_needed ?? [],
                    'status' => $this->proposal->request->status?->value ?? $this->proposal->request->status,
                    'status_label' => $this->proposal->request->status?->operatorLabel() ?? null,
                    'status_badge_class' => $this->proposal->request->status?->operatorBadgeClass() ?? null,
                    'deadline_at' => $this->proposal->request->deadline_at?->toIso8601String(),
                    'notes' => $this->proposal->request->notes,
                ] : null,
            ]),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
