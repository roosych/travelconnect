<?php

namespace App\Domain\Offers\Models;

use App\Domain\Suppliers\Enums\PriceUnit;
use App\Domain\Suppliers\Models\SupplierService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfferItem extends Model
{
    protected $table = 'offer_items';

    protected $fillable = [
        'offer_id',
        'supplier_service_id',
        'type',
        'name',
        'description',
        'quantity',
        'unit_price',
        'currency',
        'unit_price_azn',
        'exchange_rate',
        'price_unit',
    ];

    protected function casts(): array
    {
        return [
            'price_unit'     => PriceUnit::class,
            'unit_price'     => 'decimal:2',
            'unit_price_azn' => 'decimal:2',
            'exchange_rate'  => 'decimal:6',
            'quantity'       => 'integer',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class, 'offer_id');
    }

    public function supplierService(): BelongsTo
    {
        return $this->belongsTo(SupplierService::class, 'supplier_service_id');
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    public function lineTotal(): float
    {
        return round((float) $this->unit_price * $this->quantity, 2);
    }
}
