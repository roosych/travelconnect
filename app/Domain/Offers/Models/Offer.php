<?php

namespace App\Domain\Offers\Models;

use App\Domain\Attachments\Models\Attachment;
use App\Domain\Offers\Enums\OfferStatus;
use App\Domain\Proposals\Models\Proposal;
use App\Domain\RFQs\Models\Rfq;
use App\Domain\Suppliers\Models\Supplier;
use App\Support\HasPublicCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;

class Offer extends Model
{
    use HasPublicCode;

    protected string $publicCodePrefix = 'O';

    protected $table = 'offers';

    protected $fillable = [
        'rfq_id',
        'supplier_id',
        'is_partial',
        'covered_services',
        'uncovered_services',
        'unit_price',
        'currency',
        'exchange_rate',
        'unit_price_azn',
        'valid_until',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => OfferStatus::class,
            'is_partial' => 'boolean',
            'valid_until' => 'datetime',
            'unit_price' => 'decimal:2',
            'exchange_rate' => 'decimal:6',
            'unit_price_azn' => 'decimal:2',
            'covered_services' => 'array',
            'uncovered_services' => 'array',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(Rfq::class, 'rfq_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function proposals(): BelongsToMany
    {
        return $this->belongsToMany(Proposal::class, 'proposal_offer', 'offer_id', 'proposal_id')
            ->withPivot('operator_notes');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OfferItem::class, 'offer_id');
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    public function isExpired(): bool
    {
        return $this->valid_until !== null && $this->valid_until->lt(Carbon::now());
    }

    /**
     * Total calculated from line items; falls back to unit_price for legacy offers.
     */
    public function calculatedTotal(): float
    {
        if ($this->relationLoaded('items') && $this->items->isNotEmpty()) {
            return round(
                $this->items->sum(fn (OfferItem $i) => (float) $i->unit_price * $i->quantity),
                2
            );
        }

        return (float) ($this->unit_price ?? 0);
    }
}
