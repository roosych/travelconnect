<?php

namespace App\Domain\Suppliers\Models;

use App\Domain\Users\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierProfile extends Model
{
    protected $table = 'supplier_profiles';

    protected $fillable = [
        'user_id',
        'service_types',
        'description',
        'website',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'service_types' => 'array',
            'is_active'     => 'boolean',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForServiceType(Builder $query, string $type): Builder
    {
        return $query->whereJsonContains('service_types', $type);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function supportsServiceType(string $type): bool
    {
        return in_array($type, $this->service_types ?? [], true);
    }
}
