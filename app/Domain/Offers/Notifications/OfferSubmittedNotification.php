<?php

namespace App\Domain\Offers\Notifications;

use App\Domain\Notifications\Enums\NotificationCategory;
use App\Domain\Notifications\Notifications\BaseNotification;
use App\Domain\Offers\Models\Offer;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\Telegram\TelegramMessage;

/** Operator-facing: a supplier submitted an offer on an RFQ. */
class OfferSubmittedNotification extends BaseNotification
{
    public function __construct(
        public readonly Offer $offer,
    ) {}

    public function category(): NotificationCategory
    {
        return NotificationCategory::OperatorOffer;
    }

    private function rfqTitle(): string
    {
        return $this->offer->rfq?->title ?: 'RFQ #'.$this->offer->rfq_id;
    }

    private function supplierName(): ?string
    {
        return $this->offer->supplier?->name;
    }

    protected function bellTitle(): string
    {
        return 'Новый оффер';
    }

    protected function bellMessage(): string
    {
        $supplier = $this->supplierName();

        return $this->rfqTitle().($supplier ? ' — '.$supplier : '');
    }

    protected function bellUrl(): ?string
    {
        return url('/admin/offers/'.$this->offer->public_code);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Новый оффер: '.$this->rfqTitle())
            ->greeting('Поступил новый оффер')
            ->line($this->bellMessage())
            ->action('Открыть оффер', $this->bellUrl());
    }

    public function toTelegram(object $notifiable): TelegramMessage
    {
        return TelegramMessage::create("🛒 *Новый оффер*\n".$this->bellMessage())
            ->button('Открыть оффер', $this->bellUrl());
    }
}
