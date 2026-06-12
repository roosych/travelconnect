<?php

namespace App\Domain\Users\Models;

use App\Domain\Agencies\Models\Agency;
use App\Domain\Attachments\Models\Attachment;
use App\Domain\Notifications\Models\NotificationPreference;
use App\Domain\Proposals\Models\Proposal;
use App\Domain\RFQs\Models\Rfq;
use App\Domain\Suppliers\Models\Supplier;
use App\Domain\Users\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class User extends Authenticatable implements HasMedia
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, InteractsWithMedia;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'company_name',
        'phone',
        'country',
        'timezone',
        'locale',
        'currency_code',
        'telegram_chat_id',
        'telegram_username',
        'telegram_link_token',
        'telegram_linked_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'telegram_link_token',
        'telegram_chat_id',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'  => 'datetime',
            'password'           => 'hashed',
            'role'               => UserRole::class,
            'telegram_linked_at' => 'datetime',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function agencies(): BelongsToMany
    {
        return $this->belongsToMany(Agency::class, 'agency_users')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class, 'supplier_users')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function operatedRfqs(): HasMany
    {
        return $this->hasMany(Rfq::class, 'operator_id');
    }

    public function proposals(): HasMany
    {
        return $this->hasMany(Proposal::class, 'operator_id');
    }

    public function uploadedAttachments(): HasMany
    {
        return $this->hasMany(Attachment::class, 'uploader_id');
    }

    public function notificationPreferences(): HasMany
    {
        return $this->hasMany(NotificationPreference::class);
    }

    // -------------------------------------------------------------------------
    // Notification routing
    // -------------------------------------------------------------------------

    public function routeNotificationForTelegram(): ?string
    {
        return $this->telegram_chat_id;
    }

    public function isTelegramLinked(): bool
    {
        return ! empty($this->telegram_chat_id);
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

    public function isAgency(): bool
    {
        return $this->role === UserRole::Agency;
    }

    public function isOperator(): bool
    {
        return $this->role === UserRole::Operator;
    }

    public function isSupplier(): bool
    {
        return $this->role === UserRole::Supplier;
    }

    /**
     * Эффективный часовой пояс пользователя: явный timezone → пояс страны из
     * справочника → статический конфиг-фолбэк → пояс приложения (UTC).
     * В нём трактуется ввод времени и показывается вывод.
     */
    public function effectiveTimezone(): string
    {
        if ($this->timezone) {
            return $this->timezone;
        }

        if ($this->country) {
            $tz = \App\Domain\Geo\Models\Country::whereKey($this->country)->value('timezone')
                ?? config('country_timezones.'.$this->country);
            if ($tz) {
                return $tz;
            }
        }

        return config('app.timezone');
    }
}
