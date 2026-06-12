<?php

namespace App\Domain\Bookings\Models;

use App\Domain\Offers\Models\Offer;
use App\Domain\Suppliers\Models\Supplier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Immutable cost-of-goods snapshot for one service of a booking, frozen at the
 * moment the agency accepted the proposal. Values are denormalized on purpose:
 * editing or deleting the source offer/supplier later must not alter history.
 */
class BookingItem extends Model
{
    protected $table = 'booking_items';

    protected $fillable = [
        'booking_id',
        'offer_id',
        'supplier_id',
        'supplier_name',
        'service_type',
        'name',
        'description',
        'quantity',
        'net_unit_price',
        'net_currency',
        'net_fx_rate',
        'net_amount_azn',
        'markup_pct',
        'sell_amount_azn',
    ];

    protected function casts(): array
    {
        return [
            'quantity'        => 'integer',
            'net_unit_price'  => 'decimal:2',
            'net_fx_rate'     => 'decimal:6',
            'net_amount_azn'  => 'decimal:2',
            'markup_pct'      => 'decimal:2',
            'sell_amount_azn' => 'decimal:2',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class, 'offer_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
}
