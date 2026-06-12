<?php

namespace App\Domain\Requests\Notifications;

use App\Domain\Notifications\Enums\NotificationCategory;
use App\Domain\Notifications\Notifications\BaseNotification;
use App\Domain\Requests\Models\TravelRequest;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\Telegram\TelegramMessage;

/** Operator-facing: an agency submitted a new travel request. */
class RequestSubmittedNotification extends BaseNotification
{
    public function __construct(
        public readonly TravelRequest $request,
    ) {}

    public function category(): NotificationCategory
    {
        return NotificationCategory::OperatorRequest;
    }

    private function title(): string
    {
        return $this->request->title ?: 'Заявка #'.$this->request->id;
    }

    protected function bellTitle(): string
    {
        return 'Новая заявка от агентства';
    }

    protected function bellMessage(): string
    {
        return $this->title();
    }

    protected function bellUrl(): ?string
    {
        return url('/admin/requests/'.$this->request->id);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Новая заявка: '.$this->title())
            ->greeting('Новая заявка от агентства')
            ->line('Поступила новая заявка: **'.$this->title().'**')
            ->action('Открыть заявку', $this->bellUrl());
    }

    public function toTelegram(object $notifiable): TelegramMessage
    {
        return TelegramMessage::create("📝 *Новая заявка от агентства*\n".$this->title())
            ->button('Открыть заявку', $this->bellUrl());
    }
}
