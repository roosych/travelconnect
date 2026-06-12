<?php

namespace App\Domain\Clients\Models;

use App\Domain\Requests\Models\TravelRequest;
use App\Domain\Agencies\Models\Agency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $table = 'clients';

    protected $fillable = [
        'agency_id',
        'name',
        'email',
        'phone',
        'passport_number',
        'nationality',
        'date_of_birth',
        'notes',
    ];

    protected $hidden = [
        'passport_number',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class, 'agency_id');
    }

    public function travelRequests(): BelongsToMany
    {
        return $this->belongsToMany(TravelRequest::class, 'travel_request_client', 'client_id', 'travel_request_id')
            ->withPivot('is_lead');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function age(): ?int
    {
        return $this->date_of_birth?->age;
    }
}
