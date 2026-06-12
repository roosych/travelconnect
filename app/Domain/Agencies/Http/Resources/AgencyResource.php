<?php

namespace App\Domain\Agencies\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AgencyResource extends JsonResource
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
            'is_active'      => true,
            'requests_count' => $this->when(isset($this->requests_count), $this->requests_count),
            'bookings_count' => $this->when(isset($this->bookings_count), $this->bookings_count),
            'members_count'  => $this->when(isset($this->members_count), $this->members_count),
            'avatar_url'     => $this->getFirstMediaUrl('avatar') ?: null,
            'created_at'     => $this->created_at->toDateString(),
            // Только в ответе на создание: одноразовый показ пароля владельца админу.
            $this->mergeWhen(filled($this->resource->generated_password ?? null), fn () => [
                'generated_password' => $this->resource->generated_password,
            ]),
        ];
    }
}
