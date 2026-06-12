<?php

namespace App\Domain\Notifications\Notifications;

use App\Domain\Notifications\Enums\NotificationCategory;
use App\Domain\Notifications\Services\NotificationPreferenceService;
use App\Domain\Users\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

abstract class BaseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The category this notification belongs to — drives per-user channel preferences.
     */
    abstract public function category(): NotificationCategory;

    /**
     * Channels are resolved per recipient: enabled-by-preference AND routable.
     * The in-app bell (database channel) is always on for real users and is not
     * part of the configurable mail/telegram preference matrix.
     */
    public function via(object $notifiable): array
    {
        $channels = app(NotificationPreferenceService::class)
            ->channelsFor($notifiable, $this->category());

        if ($notifiable instanceof User) {
            $channels[] = 'database';
        }

        return array_values(array_unique($channels));
    }

    /**
     * Payload stored for the in-app bell. Concrete notifications provide the copy
     * via bellTitle()/bellMessage()/bellUrl().
     */
    public function toArray(object $notifiable): array
    {
        return [
            'category' => $this->category()->value,
            'icon' => $this->category()->icon(),
            'title' => $this->bellTitle(),
            'message' => $this->bellMessage(),
            'url' => $this->bellUrl(),
        ];
    }

    abstract protected function bellTitle(): string;

    abstract protected function bellMessage(): string;

    abstract protected function bellUrl(): ?string;

    /**
     * Run channel delivery on the dedicated notifications queue.
     */
    public function viaQueues(): array
    {
        return [
            'mail' => 'notifications',
            'telegram' => 'notifications',
        ];
    }
}
