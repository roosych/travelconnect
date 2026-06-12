<?php

namespace App\Domain\Suppliers\Models;

use App\Domain\Offers\Models\OfferItem;
use App\Domain\Suppliers\Enums\PriceUnit;
use App\Domain\Suppliers\Models\Supplier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class SupplierService extends Model implements HasMedia
{
    use InteractsWithMedia;
    protected $table = 'supplier_services';

    protected $fillable = [
        'supplier_id',
        'type',
        'name',
        'description',
        'capacity',
        'contact_name',
        'contact_phone',
        'base_price',
        'currency',
        'price_unit',
        'is_available',
    ];

    protected function casts(): array
    {
        return [
            'price_unit'   => PriceUnit::class,
            'base_price'   => 'decimal:2',
            'capacity'     => 'integer',
            'is_available' => 'boolean',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function offerItems(): HasMany
    {
        return $this->hasMany(OfferItem::class, 'supplier_service_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_available', true);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    // -------------------------------------------------------------------------
    // Media
    // -------------------------------------------------------------------------

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photos')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function priceLabel(): ?string
    {
        if (!$this->base_price || !$this->currency || !$this->price_unit) {
            return null;
        }
        return number_format((float) $this->base_price, 2) . ' ' . $this->currency
            . ' ' . $this->price_unit->label();
    }
}
