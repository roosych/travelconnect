<?php

namespace Database\Seeders;

use App\Domain\Offers\Enums\OfferStatus;
use App\Domain\Offers\Models\Offer;
use App\Domain\Offers\Models\OfferItem;
use App\Domain\Proposals\Enums\ProposalStatus;
use App\Domain\Proposals\Models\Proposal;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class Request5ExtraProposalSeeder extends Seeder
{
    public function run(): void
    {
        // Request 5 "Супер тур": needs transport + guide
        // RFQ#8 = transport, RFQ#9 = guide
        // Operator id=1 (Ruslan), Caspian Shuttle id=10, Baku Guide Service id=11

        // ── Offer from Caspian Shuttle for transport RFQ#8 ───────────────────
        $offerTransport = Offer::create([
            'rfq_id'             => 8,
            'supplier_id'        => 10, // Caspian Shuttle
            'is_partial'         => false,
            'covered_services'   => ['transport'],
            'uncovered_services' => [],
            'unit_price'         => 280.00,
            'currency'           => 'AZN',
            'valid_until'        => Carbon::today()->addDays(45),
            'notes'              => 'Комфортный микроавтобус Mercedes Sprinter, встреча в аэропорту.',
            'status'             => OfferStatus::Selected,
        ]);

        OfferItem::create([
            'offer_id'   => $offerTransport->id,
            'type'       => 'transport',
            'name'       => 'Трансфер аэропорт – отель (Mercedes Sprinter)',
            'quantity'   => 1,
            'unit_price' => 280.00,
            'currency'   => 'AZN',
        ]);

        // ── Offer from Baku Guide Service for guide RFQ#9 ────────────────────
        $offerGuide = Offer::create([
            'rfq_id'             => 9,
            'supplier_id'        => 11, // Baku Guide Service
            'is_partial'         => false,
            'covered_services'   => ['guide'],
            'uncovered_services' => [],
            'unit_price'         => 150.00,
            'currency'           => 'AZN',
            'valid_until'        => Carbon::today()->addDays(45),
            'notes'              => 'Русскоязычный гид, индивидуальная экскурсия по Старому городу.',
            'status'             => OfferStatus::Selected,
        ]);

        OfferItem::create([
            'offer_id'   => $offerGuide->id,
            'type'       => 'guide',
            'name'       => 'Гид по Старому городу (3 часа)',
            'quantity'   => 1,
            'unit_price' => 150.00,
            'currency'   => 'AZN',
        ]);

        // ── New draft proposal from operator for request 5 ───────────────────
        $proposal = Proposal::create([
            'request_id'  => 5,
            'operator_id' => 1, // Ruslan
            'title'       => 'Nomad Travel Almaty, Супер тур, 23.05.2026 – 25.05.2026',
            'description' => 'Альтернативный вариант от проверенных партнёров: трансфер на Sprinter и индивидуальный гид по Баку.',
            'total_price' => 0,
            'currency'    => 'AZN',
            'valid_until' => Carbon::today()->addDays(30),
            'status'      => ProposalStatus::Draft,
        ]);

        // ── Attach offers with markup ─────────────────────────────────────────
        $proposal->offers()->attach($offerTransport->id, [
            'operator_notes'      => '',
            'markup_pct'          => 0,
            'selected_item_types' => json_encode(['transport']),
            'item_markups'        => json_encode(['transport' => 15]),
        ]);

        $proposal->offers()->attach($offerGuide->id, [
            'operator_notes'      => '',
            'markup_pct'          => 0,
            'selected_item_types' => json_encode(['guide']),
            'item_markups'        => json_encode(['guide' => 10]),
        ]);

        // Recalculate total: transport 280 * 1.15 + guide 150 * 1.10
        $total = 280 * 1.15 + 150 * 1.10;
        $proposal->update(['total_price' => $total]);

        $this->command->info("Seeded proposal #{$proposal->id} with offers #{$offerTransport->id} (Caspian Shuttle) and #{$offerGuide->id} (Baku Guide Service) for request #5.");
    }
}
