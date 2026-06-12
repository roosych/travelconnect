<?php

namespace App\Domain\Suppliers\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierMemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'email'      => $this->email,
            'phone'      => $this->phone,
            'avatar_url' => $this->getFirstMediaUrl('avatar') ?: null,
            'role'       => $this->pivot->role,
            'joined_at'  => $this->pivot->created_at?->toDateString(),
        ];
    }
}
