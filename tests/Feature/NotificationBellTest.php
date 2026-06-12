<?php

namespace Tests\Feature;

use App\Domain\Notifications\Enums\NotificationCategory;
use App\Domain\Notifications\Notifications\BaseNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Messages\MailMessage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotificationBellTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_channel_is_always_added_for_users(): void
    {
        $user = User::factory()->create(['telegram_chat_id' => null]);

        $channels = (new BellTestNotification())->via($user);

        $this->assertContains('database', $channels);
    }

    public function test_notification_is_stored_for_the_in_app_bell(): void
    {
        $user = User::factory()->create(['telegram_chat_id' => null]);

        $user->notify(new BellTestNotification());

        $this->assertDatabaseCount('notifications', 1);
        $this->assertSame(1, $user->unreadNotifications()->count());
    }

    public function test_index_returns_notifications_with_unread_count(): void
    {
        $user = User::factory()->create();
        $user->notify(new BellTestNotification());

        Sanctum::actingAs($user);

        $this->getJson('/api/notifications')
            ->assertOk()
            ->assertJsonPath('unread_count', 1)
            ->assertJsonPath('data.0.title', 'Тестовое уведомление');
    }

    public function test_marking_read_clears_unread_count(): void
    {
        $user = User::factory()->create();
        $user->notify(new BellTestNotification());
        $id = $user->notifications()->first()->id;

        Sanctum::actingAs($user);

        $this->patchJson("/api/notifications/{$id}/read")
            ->assertOk()
            ->assertJsonPath('unread_count', 0);

        $this->assertSame(0, $user->fresh()->unreadNotifications()->count());
    }
}

/**
 * Minimal concrete notification — exercises the bell pipeline without depending
 * on a persisted domain model (which SerializesModels would try to reload).
 */
class BellTestNotification extends BaseNotification
{
    public function category(): NotificationCategory
    {
        return NotificationCategory::Booking;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)->line('Тест');
    }

    protected function bellTitle(): string
    {
        return 'Тестовое уведомление';
    }

    protected function bellMessage(): string
    {
        return 'Сообщение';
    }

    protected function bellUrl(): ?string
    {
        return '/agency/bookings/1';
    }
}
