<?php

namespace App\Domain\Suppliers\Models;

use App\Domain\Offers\Enums\OfferStatus;
use App\Domain\Suppliers\Enums\IncidentSeverity;
use App\Domain\Suppliers\Enums\IncidentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SupplierIncident extends Model
{
    protected $fillable = [
        'supplier_id',
        'type',
        'severity',
        'subject_type',
        'subject_id',
        'context',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'type'     => IncidentType::class,
            'severity' => IncidentSeverity::class,
            'context'  => 'array',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    // -------------------------------------------------------------------------
    // Factory helpers
    // -------------------------------------------------------------------------

    public static function recordOfferWithdrawn(
        int $supplierId,
        int $offerId,
        OfferStatus $fromStatus,
        int $rfqId,
    ): self {
        $severity = $fromStatus === OfferStatus::Selected
            ? IncidentSeverity::High
            : IncidentSeverity::Low;

        return self::create([
            'supplier_id'  => $supplierId,
            'type'         => IncidentType::OfferWithdrawn,
            'severity'     => $severity,
            'subject_type' => 'offer',
            'subject_id'   => $offerId,
            'context'      => [
                'from_status' => $fromStatus->value,
                'rfq_id'      => $rfqId,
            ],
        ]);
    }
}
