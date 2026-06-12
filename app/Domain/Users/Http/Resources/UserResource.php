<?php

namespace App\Domain\Users\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'email'        => $this->email,
            'phone'        => $this->phone,
            'role'         => $this->role->value,
            'company_name' => $this->company_name,
            'country'      => $this->country,
            'avatar_url'   => $this->getFirstMediaUrl('avatar') ?: null,
        ];
    }
}
