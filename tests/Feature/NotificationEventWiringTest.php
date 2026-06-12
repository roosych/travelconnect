<?php

namespace Tests\Feature;

use App\Domain\Bookings\Events\BookingStatusChanged;
use App\Domain\Offers\Events\OfferAccepted;
use App\Domain\Offers\Events\OfferRejected;
use App\Domain\Offers\Events\OfferSubmitted;
use App\Domain\Proposals\Events\ProposalDecided;
use App\Domain\Proposals\Events\ProposalSent;
use App\Domain\Requests\Events\RequestStatusChanged;
use App\Domain\Requests\Events\RequestSubmitted;
use App\Domain\RFQs\Events\RfqSentToSupplier;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class NotificationEventWiringTest extends TestCase
{
    /**
     * @return array<string, array{class-string}>
     */
    public static function notificationEvents(): array
    {
        return [
            'rfq sent' => [RfqSentToSupplier::class],
            'proposal sent' => [ProposalSent::class],
            'booking status' => [BookingStatusChanged::class],
            'offer accepted' => [OfferAccepted::class],
            'offer rejected' => [OfferRejected::class],
            'offer submitted' => [OfferSubmitted::class],
            'proposal decided' => [ProposalDecided::class],
            'request submitted' => [RequestSubmitted::class],
            'request status changed' => [RequestStatusChanged::class],
        ];
    }

    #[DataProvider('notificationEvents')]
    public function test_event_has_a_registered_listener(string $eventClass): void
    {
        $this->assertTrue(
            Event::hasListeners($eventClass),
            "No listener registered for {$eventClass}",
        );
    }
}
