<?php

namespace App\Domain\Notifications\Enums;

enum NotificationChannel: string
{
    case Mail = 'mail';
    case Telegram = 'telegram';

    public function label(): string
    {
        return match ($this) {
            self::Mail => 'Email',
            self::Telegram => 'Telegram',
        };
    }
}
