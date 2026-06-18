<?php

namespace App\Domain\Proposals\Models;

use App\Domain\Attachments\Models\Attachment;
use App\Domain\Bookings\Models\Booking;
use App\Domain\Offers\Models\Offer;
use App\Domain\Proposals\Enums\ProposalStatus;
use App\Domain\Requests\Models\TravelRequest;
use App\Domain\Users\Models\User;
use App\Support\HasPublicCode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;

class Proposal extends Model
{
    use HasPublicCode;

    protected string $publicCodePrefix = 'C';

    protected $table = 'proposals';

    protected $fillable = [
        'request_id',
        'operator_id',
        'title',
        'description',
        'total_price',
        'currency',
        'original_total_price',
        'original_currency',
        'exchange_rate_snapshot',
        'valid_until',
        'status',
        'accepted_at',
    ];

    protected function casts(): array
    {
        return [
            'status'                 => ProposalStatus::class,
            'valid_until'            => 'datetime',
            'accepted_at'            => 'datetime',
            'total_price'            => 'decimal:2',
            'original_total_price'   => 'decimal:2',
            'exchange_rate_snapshot' => 'decimal:6',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function request(): BelongsTo
    {
        return $this->belongsTo(TravelRequest::class, 'request_id');
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function offers(): BelongsToMany
    {
        return $this->belongsToMany(Offer::class, 'proposal_offer', 'proposal_id', 'offer_id')
            ->withPivot('operator_notes', 'markup_pct', 'selected_item_types', 'item_markups', 'shared_catalog_media_ids', 'shared_attachment_ids');
    }

    public function booking(): HasOne
    {
        return $this->hasOne(Booking::class, 'proposal_id');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [
            ProposalStatus::Draft->value,
            ProposalStatus::Sent->value,
        ]);
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    public function isExpired(): bool
    {
        return $this->valid_until !== null && $this->valid_until->lt(Carbon::now());
    }
}
