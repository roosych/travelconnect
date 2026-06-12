<?php

namespace App\Domain\Proposals\Notifications;

use App\Domain\Notifications\Enums\NotificationCategory;
use App\Domain\Notifications\Notifications\BaseNotification;
use App\Domain\Proposals\Models\Proposal;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\Telegram\TelegramMessage;

/** Operator-facing: an agency accepted or rejected a proposal. */
class ProposalDecisionOperatorNotification extends BaseNotification
{
    public function __construct(
        public readonly Proposal $proposal,
        public readonly bool $accepted,
    ) {}

    public function category(): NotificationCategory
    {
        return NotificationCategory::OperatorProposal;
    }

    private function title(): string
    {
        return $this->proposal->title ?: 'КП #'.$this->proposal->id;
    }

    protected function bellTitle(): string
    {
        return $this->accepted ? 'Агентство приняло КП' : 'Агентство отклонило КП';
    }

    protected function bellMessage(): string
    {
        return $this->accepted
            ? $this->title().' принято — создана бронь.'
            : $this->title().' отклонено агентством.';
    }

    protected function bellUrl(): ?string
    {
        return url('/admin/proposals/'.$this->proposal->id);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->bellTitle().': '.$this->title())
            ->greeting($this->bellTitle())
            ->line($this->bellMessage())
            ->action('Открыть предложение', $this->bellUrl());
    }

    public function toTelegram(object $notifiable): TelegramMessage
    {
        $icon = $this->accepted ? '✅' : '❌';

        return TelegramMessage::create("{$icon} *{$this->bellTitle()}*\n".$this->bellMessage())
            ->button('Открыть предложение', $this->bellUrl());
    }
}
