<?php

namespace App\Domain\Payments\DTO;

use App\Domain\Payments\Enums\PaymentDirection;
use Illuminate\Database\Eloquent\Model;

/**
 * Одна «цель расчёта» по payable-модели: с кем, в какую сторону и сколько должно.
 * Сумма — в валюте контрагента (amountDue) + в базовой AZN (amountDueBase) для
 * консистентной агрегации платежей.
 */
final class SettlementTarget
{
    public function __construct(
        public readonly PaymentDirection $direction,
        public readonly Model $counterparty,
        public readonly string $currency,
        public readonly float $amountDue,
        public readonly float $amountDueBase,
        public readonly ?string $label = null,
    ) {}

    /** Курс перевода суммы контрагента в базовую (AZN), по снимку payable. */
    public function baseRatio(): float
    {
        return $this->amountDue > 0 ? $this->amountDueBase / $this->amountDue : 1.0;
    }

    public function matches(\App\Domain\Payments\Models\Payment $payment): bool
    {
        return $payment->direction === $this->direction
            && $payment->counterparty_type === $this->counterparty->getMorphClass()
            && (int) $payment->counterparty_id === (int) $this->counterparty->getKey();
    }
}
