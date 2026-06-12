<?php

namespace App\Domain\RFQs\Notifications;

use App\Domain\Notifications\Enums\NotificationCategory;
use App\Domain\Notifications\Notifications\BaseNotification;
use App\Domain\RFQs\Models\Rfq;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\Telegram\TelegramMessage;

class RfqDispatchedNotification extends BaseNotification
{
    /**
     * @param string|null $magicToken  Signed token for non-portal suppliers (rfq_supplier.token).
     *                                  When null, the recipient is a portal user.
     */
    public function __construct(
        public readonly Rfq $rfq,
        public readonly ?string $magicToken = null,
    ) {}

    public function category(): NotificationCategory
    {
        return NotificationCategory::Rfq;
    }

    private function url(): string
    {
        return $this->magicToken
            ? route('supplier.rfq', $this->magicToken)
            : url('/supplier/rfqs/'.$this->rfq->id);
    }

    protected function bellTitle(): string
    {
        return 'Новый запрос цен';
    }

    protected function bellMessage(): string
    {
        return $this->rfq->title;
    }

    protected function bellUrl(): ?string
    {
        return $this->url();
    }

    public function toMail(object $notifiable): MailMessage
    {
        $deadline = $this->rfq->deadline_at?->format('d.m.Y');

        return (new MailMessage)
            ->subject('Новый запрос цен: '.$this->rfq->title)
            ->greeting('Новый запрос цен')
            ->line('Поступил новый запрос: **'.$this->rfq->title.'**')
            ->when($deadline, fn (MailMessage $m) => $m->line('Срок ответа: '.$deadline))
            ->action('Открыть запрос', $this->url())
            ->line('Пожалуйста, подготовьте предложение до истечения срока.');
    }

    public function toTelegram(object $notifiable): TelegramMessage
    {
        $deadline = $this->rfq->deadline_at?->format('d.m.Y');

        $text = "📩 *Новый запрос цен*\n".$this->rfq->title;
        if ($deadline) {
            $text .= "\nСрок ответа: {$deadline}";
        }

        return TelegramMessage::create($text)
            ->button('Открыть запрос', $this->url());
    }
}
