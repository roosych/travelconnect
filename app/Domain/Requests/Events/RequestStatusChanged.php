<?php

namespace App\Domain\Requests\Events;

use App\Domain\Requests\Enums\RequestStatus;
use App\Domain\Requests\Models\TravelRequest;
use Illuminate\Foundation\Events\Dispatchable;

/** An operator changed the status of an agency's request (e.g. took it into work / cancelled). */
class RequestStatusChanged
{
    use Dispatchable;

    public function __construct(
        public readonly TravelRequest $request,
        public readonly RequestStatus $status,
    ) {}
}
