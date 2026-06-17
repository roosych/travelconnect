<?php

namespace App\Domain\Payments\Contracts;

use Illuminate\Support\Collection;

/**
 * Модель, по которой ведутся расчёты. Объявляет свои «цели» (с кем и сколько),
 * а модуль Payments навешивает на них платежи и считает статус. Так домен
 * Payments не знает про конкретные модели (Booking и пр.).
 */
interface Settleable
{
    /** @return Collection<int, \App\Domain\Payments\DTO\SettlementTarget> */
    public function settlementTargets(): Collection;
}
