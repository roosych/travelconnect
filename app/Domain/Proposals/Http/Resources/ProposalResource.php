<?php

namespace App\Domain\Proposals\Http\Resources;

use App\Domain\Offers\Http\Resources\OfferResource;
use App\Domain\Users\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProposalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'request_id' => $this->request_id,
            'title' => $this->title,
            'description' => $this->description,
            'total_price' => $this->total_price,
            'currency' => $this->currency,
            'original_total_price' => $this->original_total_price,
            'original_currency' => $this->original_currency,
            'exchange_rate_snapshot' => $this->exchange_rate_snapshot,
            // Operator-facing AZN amount + agency-currency reference (set once sent).
            'amount_azn' => (float) ($this->original_total_price ?? $this->total_price),
            'agency_amount' => $this->original_currency ? (float) $this->total_price : null,
            'agency_currency' => $this->original_currency ? $this->currency : null,
            'valid_until' => $this->valid_until?->toIso8601String(),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'status_badge_class' => $this->status->badgeClass(),
            'is_expired' => $this->isExpired(),
            'operator' => new UserResource($this->whenLoaded('operator')),
            'offers' => OfferResource::collection($this->whenLoaded('offers')),
            'request' => $this->whenLoaded('request', fn () => [
                'id' => $this->request->id,
                'title' => $this->request->title,
                'destination' => $this->request->destination ?? null,
                'status' => $this->request->status->value,
                'services_needed' => $this->request->services_needed ?? [],
                'agency' => $this->request->relationLoaded('agency') ? [
                    'id' => $this->request->agency?->id,
                    'name' => $this->request->agency?->name,
                    'country' => $this->request->agency?->country,
                    'currency_code' => $this->request->agency?->currency_code,
                ] : null,
            ]),
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
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
