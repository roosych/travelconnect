<?php

namespace App\Domain\Requests\Models;

use App\Domain\Geo\Models\Country;
use App\Domain\Geo\Models\Destination;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Сегмент маршрута: одна страна заявки со своими датами, направлениями
 * и услугами (с требованиями).
 */
class RequestLeg extends Model
{
    protected $table = 'travel_request_legs';

    protected $fillable = [
        'travel_request_id',
        'country_code',
        'date_from',
        'date_to',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'date_from'  => 'date',
            'date_to'    => 'date',
            'sort_order' => 'integer',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function travelRequest(): BelongsTo
    {
        return $this->belongsTo(TravelRequest::class, 'travel_request_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_code', 'code');
    }

    public function destinations(): BelongsToMany
    {
        return $this->belongsToMany(Destination::class, 'travel_request_leg_destination', 'leg_id', 'destination_id')
            ->withPivot('sort_order')
            ->orderByPivot('sort_order')
            ->withTimestamps();
    }

    public function services(): HasMany
    {
        return $this->hasMany(LegService::class, 'leg_id');
    }
}
