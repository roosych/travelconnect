<?php

namespace App\Domain\Payments\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'direction'    => $this->direction->value,
            'amount'       => (float) $this->amount,
            'currency'     => $this->currency,
            'amount_base'  => (float) $this->amount_base,
            'paid_at'      => $this->paid_at?->toDateString(),
            'reference'    => $this->reference,
            'notes'        => $this->notes,
            'confirmed'    => $this->isConfirmed(),
            'confirmed_at' => $this->confirmed_at?->toIso8601String(),
            'recorded_by'  => $this->whenLoaded('recordedBy', fn () => $this->recordedBy?->name),
            'proof'        => $this->whenLoaded('attachments', fn () => $this->attachments->map(fn ($a) => [
                'id'       => $a->id,
                'filename' => $a->filename,
            ])->values()),
            'created_at'   => $this->created_at->toIso8601String(),
        ];
    }
}
