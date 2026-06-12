<?php

namespace App\Domain\Agencies\Models;

use App\Domain\Users\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class AgencyUser extends Pivot
{
    protected $table = 'agency_users';

    public $incrementing = true;

    protected $fillable = [
        'agency_id',
        'user_id',
        'role',
    ];

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
