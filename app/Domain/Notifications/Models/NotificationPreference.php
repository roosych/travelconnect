<?php

namespace App\Domain\Notifications\Models;

use App\Domain\Notifications\Enums\NotificationCategory;
use App\Domain\Notifications\Enums\NotificationChannel;
use App\Domain\Users\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    protected $fillable = [
        'user_id',
        'category',
        'channel',
        'enabled',
    ];

    protected function casts(): array
    {
        return [
            'category' => NotificationCategory::class,
            'channel'  => NotificationChannel::class,
            'enabled'  => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
