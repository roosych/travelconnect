<?php

namespace Tests\Feature;

use App\Domain\Notifications\Enums\NotificationCategory;
use App\Domain\Notifications\Services\NotificationPreferenceService;
use App\Domain\Requests\Models\TravelRequest;
use App\Domain\Requests\Notifications\RequestSubmittedNotification;
use App\Domain\Users\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OperatorNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_agency_matrix_shows_only_agency_categories(): void
    {
        $agency = User::factory()->create(['role' => UserRole::Agency->value]);

        $matrix = (new NotificationPreferenceService())->matrixFor($agency);

        $this->assertEqualsCanonicalizing(['request_status', 'proposal', 'booking'], array_keys($matrix));
        $this->assertArrayNotHasKey('rfq', $matrix);
        $this->assertArrayNotHasKey('offer', $matrix);
    }

    public function test_supplier_matrix_shows_only_supplier_categories(): void
    {
        $supplier = User::factory()->create(['role' => UserRole::Supplier->value]);

        $matrix = (new NotificationPreferenceService())->matrixFor($supplier);

        $this->assertEqualsCanonicalizing(['rfq', 'offer'], array_keys($matrix));
    }

    public function test_settings_endpoint_exposes_role_categories(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => UserRole::Agency->value]));

        $keys = array_column(
            $this->getJson('/api/settings/notifications')->assertOk()->json('categories'),
            'key',
        );

        $this->assertEqualsCanonicalizing(['request_status', 'proposal', 'booking'], $keys);
    }

    public function test_operator_notification_routes_to_mail_and_bell_not_telegram(): void
    {
        $operator = User::factory()->create([
            'role' => UserRole::Operator->value,
            'telegram_chat_id' => null,
        ]);

        $request = new TravelRequest();
        $request->forceFill(['id' => 1, 'title' => 'Тур в Габалу']);

        $channels = (new RequestSubmittedNotification($request))->via($operator);

        $this->assertEqualsCanonicalizing(['mail', 'database'], $channels);
    }
}
