<?php

namespace App\Domain\Notifications\Listeners;

use App\Domain\Proposals\Events\ProposalSent;
use App\Domain\Proposals\Notifications\ProposalSentNotification;
use Illuminate\Support\Facades\Notification;

class SendProposalNotification
{
    public function handle(ProposalSent $event): void
    {
        $proposal = $event->proposal;
        $proposal->loadMissing('request.agency.users');

        $agency = $proposal->request?->agency;
        if ($agency === null) {
            return;
        }

        $users = $agency->users;

        if ($users->isNotEmpty()) {
            Notification::send($users, new ProposalSentNotification($proposal));

            return;
        }

        if (! empty($agency->email)) {
            Notification::route('mail', $agency->email)
                ->notify(new ProposalSentNotification($proposal));
        }
    }
}
