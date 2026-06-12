<?php

namespace App\Domain\Users\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OperatorResource extends JsonResource
{
    public ?string $plainPassword = null;

    public function withPassword(string $password): static
    {
        $this->plainPassword = $password;
        return $this;
    }

    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'email'          => $this->email,
            'phone'          => $this->phone,
            'created_at'     => $this->created_at->toDateTimeString(),
            'plain_password' => $this->plainPassword,
        ];
    }
}
