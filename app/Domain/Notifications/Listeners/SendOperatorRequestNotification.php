<?php

namespace App\Domain\Notifications\Listeners;

use App\Domain\Requests\Events\RequestSubmitted;
use App\Domain\Requests\Notifications\RequestSubmittedNotification;
use App\Domain\Users\Enums\UserRole;
use App\Domain\Users\Models\User;
use Illuminate\Support\Facades\Notification;

class SendOperatorRequestNotification
{
    public function handle(RequestSubmitted $event): void
    {
        // The request isn't assigned to an operator yet — notify the whole pool.
        $operators = User::query()->where('role', UserRole::Operator)->get();

        if ($operators->isEmpty()) {
            return;
        }

        Notification::send($operators, new RequestSubmittedNotification($event->request));
    }
}
