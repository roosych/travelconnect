<?php

namespace Tests\Feature;

use App\Domain\Notifications\Enums\NotificationCategory;
use App\Domain\Notifications\Models\NotificationPreference;
use App\Domain\Notifications\Services\NotificationPreferenceService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Tests\TestCase;

class NotificationPreferenceServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): NotificationPreferenceService
    {
        return new NotificationPreferenceService();
    }

    public function test_defaults_to_all_routable_channels_when_no_rows_exist(): void
    {
        $user = User::factory()->create(['telegram_chat_id' => '12345']);

        $channels = $this->service()->channelsFor($user, NotificationCategory::Booking);

        $this->assertEqualsCanonicalizing(['mail', 'telegram'], $channels);
    }

    public function test_telegram_is_skipped_when_not_linked(): void
    {
        $user = User::factory()->create(['telegram_chat_id' => null]);

        $channels = $this->service()->channelsFor($user, NotificationCategory::Booking);

        $this->assertSame(['mail'], $channels);
    }

    public function test_disabled_channel_is_excluded(): void
    {
        $user = User::factory()->create(['telegram_chat_id' => '12345']);

        NotificationPreference::create([
            'user_id' => $user->id,
            'category' => NotificationCategory::Booking->value,
            'channel' => 'mail',
            'enabled' => false,
        ]);

        $channels = $this->service()->channelsFor($user, NotificationCategory::Booking);

        $this->assertSame(['telegram'], $channels);
    }

    public function test_anonymous_notifiable_uses_only_routed_channels(): void
    {
        $notifiable = (new AnonymousNotifiable())->route('mail', 'vendor@example.com');

        $channels = $this->service()->channelsFor($notifiable, NotificationCategory::Rfq);

        $this->assertSame(['mail'], $channels);
    }

    public function test_update_persists_matrix(): void
    {
        $user = User::factory()->create();

        $this->service()->update($user, [
            NotificationCategory::Rfq->value => ['mail' => true, 'telegram' => false],
        ]);

        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $user->id,
            'category' => 'rfq',
            'channel' => 'telegram',
            'enabled' => false,
        ]);
    }
}
