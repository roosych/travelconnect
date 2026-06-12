<?php

namespace App\Domain\Notifications\Listeners;

use App\Domain\Proposals\Events\ProposalDecided;
use App\Domain\Proposals\Notifications\ProposalDecisionOperatorNotification;
use Illuminate\Support\Facades\Notification;

class SendOperatorProposalNotification
{
    public function handle(ProposalDecided $event): void
    {
        $event->proposal->loadMissing('operator');

        $operator = $event->proposal->operator;
        if ($operator === null) {
            return;
        }

        Notification::send(
            $operator,
            new ProposalDecisionOperatorNotification($event->proposal, $event->accepted),
        );
    }
}
