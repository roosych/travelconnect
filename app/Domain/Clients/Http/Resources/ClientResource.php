<?php

namespace App\Domain\Clients\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'agency_id'       => $this->agency_id,
            'name'            => $this->name,
            'email'           => $this->email,
            'phone'           => $this->phone,
            'nationality'     => $this->nationality,
            'date_of_birth'   => $this->date_of_birth?->toDateString(),
            'passport_number' => $this->passport_number,
            'notes'           => $this->notes,
            'age'             => $this->age(),
            'created_at'      => $this->created_at->toDateString(),
        ];
    }
}
