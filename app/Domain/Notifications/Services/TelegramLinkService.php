<?php

namespace App\Domain\Notifications\Services;

use App\Domain\Users\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class TelegramLinkService
{
    /**
     * Build a deep link the user opens to connect their Telegram account.
     * Generates a one-time link token (reusing the rfq_supplier token pattern).
     */
    public function linkUrl(User $user): string
    {
        if (empty($user->telegram_link_token)) {
            $user->telegram_link_token = Str::random(40);
            $user->save();
        }

        $username = config('services.telegram.bot_username');

        return "https://t.me/{$username}?start={$user->telegram_link_token}";
    }

    /**
     * Resolve a /start <token> from the webhook and bind the chat to the user.
     */
    public function linkByToken(string $token, string $chatId, ?string $username = null): ?User
    {
        $user = User::query()->where('telegram_link_token', $token)->first();

        if ($user === null) {
            return null;
        }

        $user->telegram_chat_id = $chatId;
        $user->telegram_username = $username;
        $user->telegram_linked_at = Carbon::now();
        $user->telegram_link_token = null;
        $user->save();

        return $user;
    }

    public function unlink(User $user): void
    {
        $user->telegram_chat_id = null;
        $user->telegram_username = null;
        $user->telegram_linked_at = null;
        $user->telegram_link_token = null;
        $user->save();
    }
}
