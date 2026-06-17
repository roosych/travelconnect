<?php

namespace App\Domain\Bookings\Models;

use App\Domain\Bookings\Enums\BookingStatus;
use App\Domain\Proposals\Models\Proposal;
use App\Domain\Requests\Models\TravelRequest;
use App\Domain\Agencies\Models\Agency;
use App\Domain\Users\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    protected $table = 'bookings';

    protected $fillable = [
        'proposal_id',
        'request_id',
        'agency_id',
        'operator_id',
        'confirmed_at',
        'travel_date_from',
        'travel_date_to',
        'pax_count',
        'final_price',
        'currency',
        'cost_total_azn',
        'sell_total_azn',
        'margin_azn',
        'fx_rate_to_agency',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status'            => BookingStatus::class,
            'confirmed_at'      => 'datetime',
            'travel_date_from'  => 'date',
            'travel_date_to'    => 'date',
            'final_price'       => 'decimal:2',
            'cost_total_azn'    => 'decimal:2',
            'sell_total_azn'    => 'decimal:2',
            'margin_azn'        => 'decimal:2',
            'fx_rate_to_agency' => 'decimal:6',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class, 'proposal_id');
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(TravelRequest::class, 'request_id');
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class, 'agency_id');
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(BookingItem::class, 'booking_id');
    }
}
