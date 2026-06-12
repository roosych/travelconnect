<?php

namespace App\Domain\Offers\Notifications;

use App\Domain\Notifications\Enums\NotificationCategory;
use App\Domain\Notifications\Notifications\BaseNotification;
use App\Domain\Offers\Models\Offer;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\Telegram\TelegramMessage;

class OfferDecisionNotification extends BaseNotification
{
    public function __construct(
        public readonly Offer $offer,
        public readonly bool $accepted,
    ) {}

    public function category(): NotificationCategory
    {
        return NotificationCategory::Offer;
    }

    private function title(): string
    {
        return $this->offer->rfq?->title ?: 'Оффер #'.$this->offer->id;
    }

    private function message(): string
    {
        return $this->accepted
            ? 'Ваш оффер по «'.$this->title().'» принят оператором.'
            : 'Ваш оффер по «'.$this->title().'» отклонён.';
    }

    protected function bellTitle(): string
    {
        return $this->accepted ? 'Ваш оффер принят' : 'Ваш оффер отклонён';
    }

    protected function bellMessage(): string
    {
        return $this->message();
    }

    protected function bellUrl(): ?string
    {
        return url('/supplier/offers/'.$this->offer->id);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->accepted ? 'Ваш оффер принят' : 'Ваш оффер отклонён';

        return (new MailMessage)
            ->subject($subject.': '.$this->title())
            ->greeting($subject)
            ->line($this->message())
            ->action('Открыть оффер', url('/supplier/offers/'.$this->offer->id));
    }

    public function toTelegram(object $notifiable): TelegramMessage
    {
        $icon = $this->accepted ? '✅' : '❌';

        return TelegramMessage::create("{$icon} ".$this->message())
            ->button('Открыть оффер', url('/supplier/offers/'.$this->offer->id));
    }
}
