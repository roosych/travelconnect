<?php

namespace App\Domain\Notifications\Services;

use App\Domain\Users\Models\User;
use NotificationChannels\Telegram\Telegram;

/**
 * Handles a single Telegram update (/start linking, /stop unlink).
 * Shared by the webhook controller and the local polling command.
 */
class TelegramUpdateProcessor
{
    public function __construct(
        private readonly TelegramLinkService $links,
        private readonly Telegram $telegram,
    ) {}

    /**
     * @param array<string, mixed> $update  A raw Telegram Update object.
     * @return string|null  Short status for logging (null = ignored).
     */
    public function handle(array $update): ?string
    {
        $message = $update['message'] ?? $update['edited_message'] ?? null;
        $chatId = data_get($message, 'chat.id');
        $text = trim((string) data_get($message, 'text', ''));

        if ($chatId === null || $text === '') {
            return null;
        }

        if (str_starts_with($text, '/start')) {
            $token = trim(substr($text, strlen('/start')));
            $user = $token !== ''
                ? $this->links->linkByToken($token, (string) $chatId, data_get($message, 'from.username'))
                : null;

            $this->reply($chatId, $user
                ? '✅ Telegram привязан. Уведомления будут приходить сюда.'
                : 'Не удалось привязать аккаунт. Откройте ссылку привязки из личного кабинета ещё раз.');

            return $user ? "linked user #{$user->id}" : 'link failed';
        }

        if (str_starts_with($text, '/stop')) {
            $user = User::query()->where('telegram_chat_id', (string) $chatId)->first();
            if ($user !== null) {
                $this->links->unlink($user);
            }

            $this->reply($chatId, '🔕 Telegram отвязан. Уведомления сюда приходить не будут.');

            return $user ? "unlinked user #{$user->id}" : 'stop (no user)';
        }

        return null;
    }

    private function reply(int|string $chatId, string $text): void
    {
        $this->telegram->sendMessage(['chat_id' => $chatId, 'text' => $text]);
    }
}
