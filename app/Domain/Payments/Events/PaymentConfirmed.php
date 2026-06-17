<?php

namespace App\Domain\Payments\Events;

use App\Domain\Payments\Models\Payment;
use Illuminate\Foundation\Events\Dispatchable;

class PaymentConfirmed
{
    use Dispatchable;

    public function __construct(
        public readonly Payment $payment,
    ) {}
}
