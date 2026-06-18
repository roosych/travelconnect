<?php

namespace App\Domain\Bookings\Models;

use App\Domain\Bookings\Enums\BookingStatus;
use App\Domain\Payments\Contracts\Settleable;
use App\Domain\Payments\DTO\SettlementTarget;
use App\Domain\Payments\Enums\PaymentDirection;
use App\Domain\Proposals\Models\Proposal;
use App\Domain\Requests\Models\TravelRequest;
use App\Domain\Agencies\Models\Agency;
use App\Domain\Suppliers\Models\Supplier;
use App\Domain\Users\Models\User;
use App\Support\HasPublicCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Booking extends Model implements Settleable
{
    use HasPublicCode;

    protected string $publicCodePrefix = 'B';

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

    /**
     * Цели расчёта: одна входящая (агентство, по sell) + по одной исходящей на
     * каждого поставщика брони (по их нетто). Суммы — в валюте контрагента + AZN-база.
     */
    public function settlementTargets(): Collection
    {
        $targets = collect();

        $agency = $this->relationLoaded('agency') ? $this->agency : $this->agency()->first();
        if ($agency) {
            $targets->push(new SettlementTarget(
                PaymentDirection::Incoming,
                $agency,
                $this->currency ?: 'AZN',
                (float) $this->final_price,
                (float) $this->sell_total_azn,
            ));
        }

        $items = $this->relationLoaded('items') ? $this->items : $this->items()->get();
        foreach ($items->whereNotNull('supplier_id')->groupBy('supplier_id') as $supplierId => $group) {
            $supplier = Supplier::find($supplierId);
            if (! $supplier) {
                continue;
            }

            $currency = $group->first()->net_currency ?: 'AZN';
            $dueBase  = (float) $group->sum(fn ($i) => (float) $i->net_amount_azn);
            $due      = (float) $group->sum(fn ($i) => (float) ($i->net_unit_price ?? 0) * (int) $i->quantity);
            if ($due <= 0) {
                $due = $dueBase;
                $currency = config('payments.base_currency', 'AZN');
            }

            $targets->push(new SettlementTarget(
                PaymentDirection::Outgoing,
                $supplier,
                $currency,
                $due,
                $dueBase,
                $group->first()->supplier_name,
            ));
        }

        return $targets;
    }
}
