<?php

namespace App\Domain\Suppliers\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierIncidentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'type'         => $this->type->value,
            'type_label'   => $this->type->label(),
            'severity'     => $this->severity->value,
            'severity_label'      => $this->severity->label(),
            'severity_badge_class'=> $this->severity->badgeClass(),
            'subject_type' => $this->subject_type,
            'subject_id'   => $this->subject_id,
            'context'      => $this->context,
            'notes'        => $this->notes,
            'created_at'   => $this->created_at?->format('d.m.Y H:i'),
        ];
    }
}
