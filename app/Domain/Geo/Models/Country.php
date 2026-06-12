<?php

namespace App\Domain\Geo\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    protected $table = 'countries';
    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'name',
        'timezone',
        'is_active',
        'available_for_requests',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active'              => 'boolean',
            'available_for_requests' => 'boolean',
            'sort_order'             => 'integer',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function destinations(): HasMany
    {
        return $this->hasMany(Destination::class, 'country_code', 'code');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /** Страны-партнёры: доступны при заведении агентств/сапплаеров. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** Страны-направления: доступны в заявках и для сапплаеров. */
    public function scopeForRequests(Builder $query): Builder
    {
        return $query->where('available_for_requests', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
