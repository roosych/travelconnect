<?php

namespace App\Domain\Offers\Http\Resources;

use App\Domain\Services\ServiceCatalog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfferItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isSupplier = $request->user()?->isSupplier() ?? false;
        // Дезинтермедиация/маржа: агентству не отдаём нетто-стоимость по позициям.
        $isAgency = $request->user()?->isAgency() ?? false;

        // Operators/agencies work in AZN (snapshot); suppliers see their own currency.
        $aznUnit = (float) ($this->unit_price_azn ?? $this->unit_price);
        $unit = $isSupplier ? (float) $this->unit_price : $aznUnit;
        $currency = $isSupplier ? $this->currency : 'AZN';

        return [
            'id' => $this->id,
            'offer_id' => $this->offer_id,
            'supplier_service_id' => $this->supplier_service_id,
            'type' => $this->type,
            'type_label' => app(ServiceCatalog::class)->typeLabel($this->type),
            'name' => $this->name,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'unit_price' => $this->when(! $isAgency, fn () => $unit),
            'currency' => $this->when(! $isAgency, fn () => $currency),
            'supplier_unit_price' => $this->when(! $isAgency, fn () => (float) $this->unit_price),
            'supplier_currency' => $this->when(! $isAgency, fn () => $this->currency),
            'unit_price_azn' => $this->when(! $isAgency, fn () => $aznUnit),
            'price_unit' => $this->price_unit->value,
            'price_unit_label' => $this->price_unit->label(),
            'line_total' => $this->when(! $isAgency, fn () => round($unit * $this->quantity, 2)),
            'catalog_name' => $this->when(
                $this->relationLoaded('supplierService') && $this->supplierService,
                fn () => $this->supplierService->name
            ),
            'catalog_description' => $this->when(
                $this->relationLoaded('supplierService') && $this->supplierService,
                fn () => $this->supplierService->description
            ),
            'catalog_photos' => $this->when(
                $this->relationLoaded('supplierService') && $this->supplierService,
                fn () => $this->supplierService->getMedia('photos')
                    ->map(fn ($m) => $m->getUrl())
                    ->values()
                    ->all()
            ),
        ];
    }
}
