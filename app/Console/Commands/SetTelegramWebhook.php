<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SetTelegramWebhook extends Command
{
    protected $signature = 'telegram:set-webhook {base? : Public base URL (e.g. https://abc.ngrok.io). Defaults to APP_URL}';

    protected $description = 'Register the Telegram webhook URL with the Bot API';

    public function handle(): int
    {
        $token = (string) config('services.telegram-bot-api.token');
        $secret = (string) config('services.telegram.webhook_secret');

        if ($token === '') {
            $this->error('TELEGRAM_BOT_TOKEN не задан.');

            return self::FAILURE;
        }

        if ($secret === '') {
            $this->error('TELEGRAM_WEBHOOK_SECRET не задан.');

            return self::FAILURE;
        }

        $base = rtrim((string) ($this->argument('base') ?: config('app.url')), '/');

        if (! str_starts_with($base, 'https://') || str_contains($base, 'localhost') || str_contains($base, '127.0.0.1')) {
            $this->error("Telegram требует публичный HTTPS-адрес. Получен: {$base}");
            $this->line('Запустите туннель (ngrok/cloudflared) и передайте его URL:');
            $this->line('  php artisan telegram:set-webhook https://<ваш-туннель>');

            return self::FAILURE;
        }

        $url = $base.'/api/telegram/webhook/'.$secret;

        $response = Http::asJson()->post("https://api.telegram.org/bot{$token}/setWebhook", [
            'url' => $url,
            'secret_token' => $secret,
            'allowed_updates' => ['message'],
        ]);

        $this->line($response->body());

        return $response->successful() ? self::SUCCESS : self::FAILURE;
    }
}
