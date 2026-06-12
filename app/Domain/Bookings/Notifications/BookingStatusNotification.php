<?php

namespace App\Domain\Bookings\Notifications;

use App\Domain\Bookings\Enums\BookingStatus;
use App\Domain\Bookings\Models\Booking;
use App\Domain\Notifications\Enums\NotificationCategory;
use App\Domain\Notifications\Notifications\BaseNotification;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\Telegram\TelegramMessage;

class BookingStatusNotification extends BaseNotification
{
    public function __construct(
        public readonly Booking $booking,
        public readonly BookingStatus $status,
    ) {}

    public function category(): NotificationCategory
    {
        return NotificationCategory::Booking;
    }

    private function url(): string
    {
        return url('/agency/bookings/'.$this->booking->id);
    }

    /**
     * Short, status-specific message line shown in both channels.
     */
    private function message(): string
    {
        return match ($this->status) {
            BookingStatus::AwaitingPayment => 'Выставлен счёт на оплату по брони #'.$this->booking->id.'.',
            BookingStatus::Paid            => 'Оплата по брони #'.$this->booking->id.' получена.',
            BookingStatus::Completed       => 'Бронь #'.$this->booking->id.' завершена.',
            BookingStatus::Cancelled       => 'Бронь #'.$this->booking->id.' отменена.',
            default                        => 'Статус брони #'.$this->booking->id.' изменён: '.$this->status->agencyLabel().'.',
        };
    }

    protected function bellTitle(): string
    {
        return 'Бронь #'.$this->booking->id.': '.$this->status->agencyLabel();
    }

    protected function bellMessage(): string
    {
        return $this->message();
    }

    protected function bellUrl(): ?string
    {
        return $this->url();
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Бронь #'.$this->booking->id.': '.$this->status->agencyLabel())
            ->greeting('Обновление по брони #'.$this->booking->id)
            ->line($this->message())
            ->action('Открыть бронь', $this->url());
    }

    public function toTelegram(object $notifiable): TelegramMessage
    {
        return TelegramMessage::create("🧾 *Обновление по брони #{$this->booking->id}*\n".$this->message())
            ->button('Открыть бронь', $this->url());
    }
}
