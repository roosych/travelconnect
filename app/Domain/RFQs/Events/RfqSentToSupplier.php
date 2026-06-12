<?php

namespace App\Domain\RFQs\Events;

use App\Domain\RFQs\Models\Rfq;
use App\Domain\Suppliers\Models\Supplier;
use Illuminate\Foundation\Events\Dispatchable;

class RfqSentToSupplier
{
    use Dispatchable;

    public function __construct(
        public readonly Rfq $rfq,
        public readonly Supplier $supplier,
    ) {}
}
