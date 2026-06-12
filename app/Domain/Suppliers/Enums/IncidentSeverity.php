<?php

namespace App\Domain\Suppliers\Enums;

enum IncidentSeverity: string
{
    case Low  = 'low';
    case High = 'high';

    public function label(): string
    {
        return match($this) {
            self::Low  => 'Низкая',
            self::High => 'Высокая',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Low  => 'badge-light-warning',
            self::High => 'badge-light-danger',
        };
    }
}
