<?php

namespace App\Domain\Suppliers\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'email'          => $this->email,
            'phone'          => $this->phone,
            'country'        => $this->country,
            'currency_code'  => $this->currency_code,
            'service_types'  => $this->service_types ?? [],
            'description'    => $this->description,
            'website'        => $this->website,
            'is_active'          => $this->is_active,
            'accepting_requests' => $this->accepting_requests,
            'uses_portal'        => $this->uses_portal,
            'offers_count'   => $this->when(isset($this->offers_count), $this->offers_count),
            'members_count'  => $this->when(isset($this->members_count), $this->members_count),
            'avatar_url'     => $this->getFirstMediaUrl('avatar') ?: null,
            'created_at'     => $this->created_at->toDateString(),
        ];
    }
}
