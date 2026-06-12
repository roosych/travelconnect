<?php

namespace App\Domain\Requests\Notifications;

use App\Domain\Notifications\Enums\NotificationCategory;
use App\Domain\Notifications\Notifications\BaseNotification;
use App\Domain\Requests\Enums\RequestStatus;
use App\Domain\Requests\Models\TravelRequest;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\Telegram\TelegramMessage;

/** Agency-facing: the status of the agency's own request changed (operator-driven). */
class RequestStatusNotification extends BaseNotification
{
    public function __construct(
        public readonly TravelRequest $request,
        public readonly RequestStatus $status,
    ) {}

    public function category(): NotificationCategory
    {
        return NotificationCategory::Request;
    }

    private function title(): string
    {
        return $this->request->title ?: 'Заявка #'.$this->request->id;
    }

    protected function bellTitle(): string
    {
        return match ($this->status) {
            RequestStatus::Processing => 'Заявка взята в работу',
            RequestStatus::Cancelled => 'Заявка отменена',
            default => 'Статус заявки изменён',
        };
    }

    protected function bellMessage(): string
    {
        return match ($this->status) {
            RequestStatus::Processing => $this->title().' — оператор начал работу.',
            RequestStatus::Cancelled => $this->title().' отменена оператором.',
            default => $this->title(),
        };
    }

    protected function bellUrl(): ?string
    {
        return url('/agency/requests/'.$this->request->id);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->bellTitle().': '.$this->title())
            ->greeting($this->bellTitle())
            ->line($this->bellMessage())
            ->action('Открыть заявку', $this->bellUrl());
    }

    public function toTelegram(object $notifiable): TelegramMessage
    {
        return TelegramMessage::create("📄 *{$this->bellTitle()}*\n".$this->bellMessage())
            ->button('Открыть заявку', $this->bellUrl());
    }
}
