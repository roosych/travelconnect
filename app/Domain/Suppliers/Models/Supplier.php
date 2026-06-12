<?php

namespace App\Domain\Suppliers\Models;

use App\Domain\Offers\Models\Offer;
use App\Domain\Users\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Domain\Suppliers\Enums\IncidentSeverity;
use App\Domain\Suppliers\Models\SupplierIncident;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Supplier extends Model implements HasMedia
{
    use InteractsWithMedia, SoftDeletes;

    protected $table = 'suppliers';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'country',
        'currency_code',
        'service_types',
        'description',
        'website',
        'is_active',
        'accepting_requests',
        'uses_portal',
    ];

    protected function casts(): array
    {
        return [
            'service_types'      => 'array',
            'is_active'          => 'boolean',
            'accepting_requests' => 'boolean',
            'uses_portal'        => 'boolean',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'supplier_users')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function members(): HasMany
    {
        return $this->hasMany(SupplierUser::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(SupplierService::class, 'supplier_id');
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class, 'supplier_id');
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(SupplierIncident::class, 'supplier_id');
    }

    public function highSeverityIncidents(): HasMany
    {
        return $this->hasMany(SupplierIncident::class, 'supplier_id')
            ->where('severity', IncidentSeverity::High);
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

    public function scopeForCountry(Builder $query, string $code): Builder
    {
        return $query->where('country', $code);
    }

    /** Поставщик не на самопаузе — готов получать запросы. */
    public function scopeAcceptingRequests(Builder $query): Builder
    {
        return $query->where('accepting_requests', true);
    }

    /**
     * Кандидаты на авторассылку RFQ по паре (страна × тип услуги):
     * активен, в нужной стране, поддерживает тип услуги и не на паузе.
     */
    public function scopeReceivable(Builder $query, string $country, string $type): Builder
    {
        return $query->active()
            ->acceptingRequests()
            ->forCountry($country)
            ->forServiceType($type);
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

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function supportsServiceType(string $type): bool
    {
        return in_array($type, $this->service_types ?? [], true);
    }

    /** Готов ли поставщик получать запросы (активен и не на самопаузе). */
    public function isReceivable(): bool
    {
        return $this->is_active && $this->accepting_requests;
    }

    public function isPortalUser(): bool
    {
        return $this->uses_portal;
    }
}
