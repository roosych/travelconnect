<?php

namespace App\Domain\RFQs\Models;

use App\Domain\Attachments\Models\Attachment;
use App\Domain\Offers\Models\Offer;
use App\Domain\Requests\Models\TravelRequest;
use App\Domain\RFQs\Enums\RfqStatus;
use App\Domain\Users\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Rfq extends Model
{
    protected $table = 'rfqs';

    protected $fillable = [
        'request_id',
        'country_code',
        'leg_id',
        'operator_id',
        'title',
        'description',
        'service_type',
        'deadline_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status'      => RfqStatus::class,
            'deadline_at' => 'datetime',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function request(): BelongsTo
    {
        return $this->belongsTo(TravelRequest::class, 'request_id');
    }

    public function leg(): BelongsTo
    {
        return $this->belongsTo(\App\Domain\Requests\Models\RequestLeg::class, 'leg_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(\App\Domain\Geo\Models\Country::class, 'country_code', 'code');
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(\App\Domain\Suppliers\Models\Supplier::class, 'rfq_supplier', 'rfq_id', 'supplier_id')
            ->withPivot('sent_at', 'token', 'token_expires_at', 'service_types', 'notes');
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class, 'rfq_id');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function sharedAttachments(): BelongsToMany
    {
        return $this->belongsToMany(Attachment::class, 'rfq_shared_attachments', 'rfq_id', 'attachment_id');
    }
}
