<?php

namespace App\Domain\Notifications\Services;

use App\Domain\Notifications\Enums\NotificationCategory;
use App\Domain\Notifications\Enums\NotificationChannel;
use App\Domain\Notifications\Models\NotificationPreference;
use App\Domain\Users\Models\User;
use Illuminate\Notifications\AnonymousNotifiable;

class NotificationPreferenceService
{
    /**
     * Resolve which channels a notification of the given category should use for
     * this recipient: enabled-by-preference AND technically routable.
     *
     * @return list<string> channel values, e.g. ['mail', 'telegram']
     */
    public function channelsFor(object $notifiable, NotificationCategory $category): array
    {
        // Non-portal recipients (e.g. supplier email without an account): only the
        // explicitly-routed channels apply, and preferences don't exist for them.
        if ($notifiable instanceof AnonymousNotifiable) {
            return array_keys($notifiable->routes);
        }

        if (! $notifiable instanceof User) {
            return [];
        }

        $prefs = NotificationPreference::query()
            ->where('user_id', $notifiable->id)
            ->where('category', $category->value)
            ->get()
            ->keyBy(fn (NotificationPreference $p) => $p->channel->value);

        $channels = [];

        foreach (NotificationChannel::cases() as $channel) {
            $row = $prefs->get($channel->value);
            $enabled = $row ? $row->enabled : true; // absence of a row = enabled

            if (! $enabled || ! $this->isRoutable($notifiable, $channel)) {
                continue;
            }

            $channels[] = $channel->value;
        }

        return $channels;
    }

    /**
     * Notification categories relevant to this user's role (drives the settings UI).
     *
     * @return list<NotificationCategory>
     */
    public function categoriesForUser(User $user): array
    {
        $audience = match (true) {
            $user->isSupplier() => 'supplier',
            $user->isOperator() => 'operator',
            default => 'agency',
        };

        return NotificationCategory::forAudience($audience);
    }

    /**
     * Full preference matrix for the settings UI: category => channel => bool.
     *
     * @return array<string, array<string, bool>>
     */
    public function matrixFor(User $user): array
    {
        $rows = NotificationPreference::query()
            ->where('user_id', $user->id)
            ->get()
            ->keyBy(fn (NotificationPreference $p) => $p->category->value.'.'.$p->channel->value);

        $matrix = [];

        foreach ($this->categoriesForUser($user) as $category) {
            foreach (NotificationChannel::cases() as $channel) {
                $row = $rows->get($category->value.'.'.$channel->value);
                $matrix[$category->value][$channel->value] = $row ? $row->enabled : true;
            }
        }

        return $matrix;
    }

    /**
     * Persist the matrix coming from the settings UI.
     *
     * @param array<string, array<string, bool>> $matrix category => channel => bool
     */
    public function update(User $user, array $matrix): void
    {
        foreach ($matrix as $categoryValue => $channels) {
            $category = NotificationCategory::tryFrom((string) $categoryValue);
            if ($category === null) {
                continue;
            }

            foreach ($channels as $channelValue => $enabled) {
                $channel = NotificationChannel::tryFrom((string) $channelValue);
                if ($channel === null) {
                    continue;
                }

                NotificationPreference::updateOrCreate(
                    ['user_id' => $user->id, 'category' => $category->value, 'channel' => $channel->value],
                    ['enabled' => (bool) $enabled],
                );
            }
        }
    }

    private function isRoutable(User $user, NotificationChannel $channel): bool
    {
        return match ($channel) {
            NotificationChannel::Mail => ! empty($user->email),
            NotificationChannel::Telegram => ! empty($user->telegram_chat_id),
        };
    }
}
