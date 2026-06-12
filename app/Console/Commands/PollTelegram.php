<?php

namespace App\Console\Commands;

use App\Domain\Notifications\Services\TelegramUpdateProcessor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Local-dev alternative to the webhook: long-polls getUpdates and processes
 * /start linking and /stop unlinking without needing a public HTTPS URL.
 */
class PollTelegram extends Command
{
    protected $signature = 'telegram:poll {--timeout=30 : Long-poll timeout (seconds)}';

    protected $description = 'Poll Telegram for /start and /stop messages (local dev, no webhook needed)';

    public function handle(TelegramUpdateProcessor $processor): int
    {
        $token = (string) config('services.telegram-bot-api.token');

        if ($token === '') {
            $this->error('TELEGRAM_BOT_TOKEN не задан.');

            return self::FAILURE;
        }

        $pollTimeout = (int) $this->option('timeout');
        $offset = null;

        $this->info('Слушаю Telegram (getUpdates). Ctrl+C для остановки.');

        while (true) {
            try {
                $response = Http::timeout($pollTimeout + 10)
                    ->get("https://api.telegram.org/bot{$token}/getUpdates", array_filter([
                        'timeout' => $pollTimeout,
                        'offset' => $offset,
                        'allowed_updates' => json_encode(['message']),
                    ], fn ($v) => $v !== null));
            } catch (\Throwable $e) {
                $this->warn('Сетевая ошибка: '.$e->getMessage().' — повтор через 3с');
                sleep(3);

                continue;
            }

            if ($response->json('ok') !== true) {
                // 409 = webhook is set; getUpdates and webhook are mutually exclusive.
                $this->error('Telegram: '.$response->body());
                sleep(3);

                continue;
            }

            foreach ($response->json('result', []) as $update) {
                $offset = ($update['update_id'] ?? 0) + 1;
                try {
                    $status = $processor->handle($update);
                    if ($status !== null) {
                        $this->line('  • '.$status);
                    }
                } catch (\Throwable $e) {
                    $this->error('  ! '.$e->getMessage());
                }
            }
        }
    }
}
