<?php

namespace App\Domain\Services\Models;

use App\Domain\Services\Observers\ServiceTypeObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Lang;

/**
 * Тип услуги (динамический справочник вместо enum). `code` неизменяем — на нём
 * завязан матчинг поставщиков и ключи requirements. `name` — дефолтный лейбл
 * (англ), используется как фолбэк когда нет перевода по ключу.
 */
#[ObservedBy([ServiceTypeObserver::class])]
class ServiceType extends Model
{
    protected $fillable = [
        'code',
        'name',
        'default_markup_pct',
        'is_active',
        'available_for_requests',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'default_markup_pct'     => 'decimal:2',
            'is_active'              => 'boolean',
            'available_for_requests' => 'boolean',
        ];
    }

    public function serviceAttributes(): HasMany
    {
        return $this->hasMany(ServiceAttribute::class)->orderBy('sort_order')->orderBy('id');
    }

    /** Локализованный лейбл: перевод по ключу, фолбэк на дефолт из БД. */
    public function label(): string
    {
        $key = "services.types.{$this->code}";

        return Lang::has($key) ? __($key) : $this->name;
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    /** Типы, доступные при создании заявки. */
    public function scopeRequestable(Builder $q): Builder
    {
        return $q->where('is_active', true)->where('available_for_requests', true);
    }

    public function scopeOrdered(Builder $q): Builder
    {
        return $q->orderBy('sort_order')->orderBy('id');
    }
}
