<?php

namespace App\Domain\Requests\Events;

use App\Domain\Requests\Models\TravelRequest;
use Illuminate\Foundation\Events\Dispatchable;

/** An agency submitted a travel request (not yet assigned to an operator). */
class RequestSubmitted
{
    use Dispatchable;

    public function __construct(
        public readonly TravelRequest $request,
    ) {}
}
