<?php

namespace App\Domain\Settings\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $table = 'currencies';
    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'name',
        'rate',
        'is_active',
        'is_default',
        'rates_updated_at',
    ];

    protected function casts(): array
    {
        return [
            'rate'             => 'decimal:6',
            'is_active'        => 'boolean',
            'is_default'       => 'boolean',
            'rates_updated_at' => 'datetime',
        ];
    }
}
