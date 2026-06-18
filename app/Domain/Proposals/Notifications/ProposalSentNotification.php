<?php

namespace App\Domain\Proposals\Notifications;

use App\Domain\Notifications\Enums\NotificationCategory;
use App\Domain\Notifications\Notifications\BaseNotification;
use App\Domain\Proposals\Models\Proposal;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\Telegram\TelegramMessage;

class ProposalSentNotification extends BaseNotification
{
    public function __construct(
        public readonly Proposal $proposal,
    ) {}

    public function category(): NotificationCategory
    {
        return NotificationCategory::Proposal;
    }

    private function url(): string
    {
        return url('/agency/requests/'.$this->proposal->request->public_code);
    }

    protected function bellTitle(): string
    {
        return 'Новое коммерческое предложение';
    }

    protected function bellMessage(): string
    {
        return $this->proposal->title ?: 'Предложение #'.$this->proposal->id;
    }

    protected function bellUrl(): ?string
    {
        return $this->url();
    }

    public function toMail(object $notifiable): MailMessage
    {
        $title = $this->proposal->title ?: 'Предложение #'.$this->proposal->id;

        return (new MailMessage)
            ->subject('Новое коммерческое предложение: '.$title)
            ->greeting('Новое коммерческое предложение')
            ->line('По вашей заявке подготовлено предложение: **'.$title.'**')
            ->action('Посмотреть предложение', $this->url())
            ->line('Вы можете принять или отклонить его в личном кабинете.');
    }

    public function toTelegram(object $notifiable): TelegramMessage
    {
        $title = $this->proposal->title ?: 'Предложение #'.$this->proposal->id;

        return TelegramMessage::create("💼 *Новое коммерческое предложение*\n".$title)
            ->button('Посмотреть', $this->url());
    }
}
