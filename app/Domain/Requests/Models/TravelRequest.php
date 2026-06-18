<?php

namespace App\Domain\Requests\Models;

use App\Domain\Attachments\Models\Attachment;
use App\Domain\Bookings\Models\Booking;
use App\Domain\Clients\Models\Client;
use App\Domain\Proposals\Models\Proposal;
use App\Domain\Requests\Enums\RequestStatus;
use App\Domain\RFQs\Models\Rfq;
use App\Domain\Agencies\Models\Agency;
use App\Support\HasPublicCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class TravelRequest extends Model implements HasMedia
{
    use HasPublicCode;
    use InteractsWithMedia;

    protected string $publicCodePrefix = 'R';

    protected $table = 'travel_requests';

    protected $fillable = [
        'agency_id',
        'title',
        'destination',
        'travel_date_from',
        'travel_date_to',
        'pax_count',
        'services_needed',
        'notes',
        'deadline_at',
        'status',
        'pax_count_changed_at',
    ];

    protected function casts(): array
    {
        return [
            'status'              => RequestStatus::class,
            'travel_date_from'    => 'date',
            'travel_date_to'      => 'date',
            'deadline_at'          => 'datetime',
            'pax_count_changed_at' => 'datetime',
            'services_needed'      => 'array',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class, 'agency_id');
    }

    public function rfqs(): HasMany
    {
        return $this->hasMany(Rfq::class, 'request_id');
    }

    public function proposals(): HasMany
    {
        return $this->hasMany(Proposal::class, 'request_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'request_id');
    }

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'travel_request_client', 'travel_request_id', 'client_id')
            ->withPivot('is_lead');
    }

    public function legs(): HasMany
    {
        return $this->hasMany(RequestLeg::class, 'travel_request_id')->orderBy('sort_order');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function leadClient(): ?Client
    {
        return $this->clients()->wherePivot('is_lead', true)->first();
    }

    // -------------------------------------------------------------------------
    // Media
    // -------------------------------------------------------------------------

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'application/pdf']);
    }
}
