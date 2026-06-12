<?php

namespace App\Domain\Agencies\Models;

use App\Domain\Bookings\Models\Booking;
use App\Domain\Clients\Models\Client;
use App\Domain\Requests\Models\TravelRequest;
use App\Domain\Users\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Agency extends Model implements HasMedia
{
    use InteractsWithMedia, SoftDeletes;

    protected $table = 'agencies';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'country',
        'currency_code',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'agency_users')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function members(): HasMany
    {
        return $this->hasMany(AgencyUser::class);
    }

    public function travelRequests(): HasMany
    {
        return $this->hasMany(TravelRequest::class, 'agency_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'agency_id');
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class, 'agency_id');
    }

    // -------------------------------------------------------------------------
    // Media
    // -------------------------------------------------------------------------

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }
}
