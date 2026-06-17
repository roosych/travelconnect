<?php

namespace App\Domain\Payments\Models;

use App\Domain\Attachments\Models\Attachment;
use App\Domain\Payments\Enums\PaymentDirection;
use App\Domain\Users\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'payable_type', 'payable_id',
        'direction',
        'counterparty_type', 'counterparty_id',
        'amount', 'currency', 'amount_base', 'fx_rate',
        'paid_at', 'reference', 'notes',
        'recorded_by', 'confirmed_at', 'confirmed_by',
    ];

    protected function casts(): array
    {
        return [
            'direction'    => PaymentDirection::class,
            'amount'       => 'decimal:2',
            'amount_base'  => 'decimal:2',
            'fx_rate'      => 'decimal:8',
            'paid_at'      => 'date',
            'confirmed_at' => 'datetime',
        ];
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function counterparty(): MorphTo
    {
        return $this->morphTo();
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function isConfirmed(): bool
    {
        return $this->confirmed_at !== null;
    }

    public function scopeConfirmed(Builder $q): Builder
    {
        return $q->whereNotNull('confirmed_at');
    }
}
