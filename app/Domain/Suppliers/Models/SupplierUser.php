<?php

namespace App\Domain\Suppliers\Models;

use App\Domain\Users\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class SupplierUser extends Pivot
{
    protected $table = 'supplier_users';

    public $incrementing = true;

    protected $fillable = [
        'supplier_id',
        'user_id',
        'role',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
