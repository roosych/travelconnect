<?php

namespace App\Domain\Payments\Enums;

enum PaymentDirection: string
{
    case Incoming = 'incoming'; // деньги к оператору (от агентства)
    case Outgoing = 'outgoing'; // деньги от оператора (поставщику)

    public function label(): string
    {
        return __('payments.direction.'.$this->value);
    }
}
