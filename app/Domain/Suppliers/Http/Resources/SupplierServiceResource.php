<?php

namespace App\Domain\Suppliers\Http\Resources;

use App\Domain\Services\ServiceCatalog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'supplier_id'         => $this->supplier_id,
            'type'                => $this->type,
            'type_label'          => app(ServiceCatalog::class)->typeLabel($this->type),
            'name'                => $this->name,
            'description'         => $this->description,
            'capacity'            => $this->capacity,
            'contact_name'        => $this->contact_name,
            'contact_phone'       => $this->contact_phone,
            'base_price'          => $this->base_price ? (float) $this->base_price : null,
            'currency'            => $this->currency,
            'price_unit'          => $this->price_unit?->value,
            'price_unit_label'    => $this->price_unit?->label(),
            'price_label'         => $this->priceLabel(),
            'is_available'        => $this->is_available,
            'created_at'          => $this->created_at->toDateString(),
            'photos'              => $this->getMedia('photos')->map(fn ($m) => [
                'id'  => $m->id,
                'url' => $m->getUrl(),
            ])->values(),
        ];
    }
}
