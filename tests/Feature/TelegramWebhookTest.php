<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use NotificationChannels\Telegram\Telegram;
use Tests\TestCase;

class TelegramWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.telegram.webhook_secret' => 'shh']);

        // The package's Telegram client would hit the real API on reply; stub it.
        $this->mock(Telegram::class, function ($mock) {
            $mock->shouldReceive('sendMessage')->andReturn(null);
        });
    }

    public function test_start_command_links_telegram_account(): void
    {
        $user = User::factory()->create([
            'telegram_link_token' => 'link-token-123',
            'telegram_chat_id' => null,
        ]);

        $response = $this->postJson('/api/telegram/webhook/shh', [
            'message' => [
                'chat' => ['id' => 99887766],
                'from' => ['username' => 'tourpro'],
                'text' => '/start link-token-123',
            ],
        ]);

        $response->assertOk();

        $user->refresh();
        $this->assertSame('99887766', $user->telegram_chat_id);
        $this->assertSame('tourpro', $user->telegram_username);
        $this->assertNull($user->telegram_link_token);
        $this->assertNotNull($user->telegram_linked_at);
    }

    public function test_wrong_secret_is_rejected(): void
    {
        $this->postJson('/api/telegram/webhook/wrong', [
            'message' => ['chat' => ['id' => 1], 'text' => '/start x'],
        ])->assertNotFound();
    }

    public function test_stop_command_unlinks_account(): void
    {
        $user = User::factory()->create([
            'telegram_chat_id' => '55555',
            'telegram_username' => 'tourpro',
        ]);

        $this->postJson('/api/telegram/webhook/shh', [
            'message' => [
                'chat' => ['id' => 55555],
                'text' => '/stop',
            ],
        ])->assertOk();

        $user->refresh();
        $this->assertNull($user->telegram_chat_id);
    }
}
