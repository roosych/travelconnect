<?php

namespace App\Domain\Services\Models;

use App\Domain\Services\Observers\ServiceAttributeObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Lang;

/**
 * Атрибут (подкатегория) типа услуги. В матчинге не участвует — заполняется
 * агентством в заявке, хранится в leg_services.requirements по ключу `code`.
 */
#[ObservedBy([ServiceAttributeObserver::class])]
class ServiceAttribute extends Model
{
    /** Допустимые типы инпута. */
    public const INPUT_TYPES = ['select', 'multiselect', 'boolean', 'number', 'text', 'textarea'];

    protected $fillable = [
        'service_type_id',
        'code',
        'name',
        'input_type',
        'options',
        'config',
        'is_required',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'options'     => 'array',
            'config'      => 'array',
            'is_required' => 'boolean',
            'is_active'   => 'boolean',
        ];
    }

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    /** Локализованный лейбл атрибута (требует загруженный serviceType). */
    public function label(): string
    {
        $key = "services.attrs.{$this->serviceType->code}.{$this->code}";

        return Lang::has($key) ? __($key) : $this->name;
    }
}
