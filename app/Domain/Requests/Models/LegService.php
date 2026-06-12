<?php

namespace App\Domain\Requests\Models;

use App\Domain\Services\ServiceCatalog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Услуга, нужная в рамках сегмента, со структурированными требованиями
 * (requirements) под её тип:
 *  - accommodation: { stars, board, room_types }
 *  - transport:     { vehicle_type, min_capacity, with_driver }
 *  - guide:         { languages, gender, licensed }
 */
class LegService extends Model
{
    protected $table = 'leg_services';

    protected $fillable = [
        'leg_id',
        'service_type',
        'requirements',
    ];

    protected function casts(): array
    {
        return [
            'requirements' => 'array',
        ];
    }

    public function leg(): BelongsTo
    {
        return $this->belongsTo(RequestLeg::class, 'leg_id');
    }

    /** Человекочитаемая сводка требований под язык смотрящего, напр. «4★ · HB — полупансион». */
    public function requirementsSummary(): string
    {
        return app(ServiceCatalog::class)->summary($this->service_type, $this->requirements ?? []);
    }
}
