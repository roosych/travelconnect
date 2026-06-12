<?php

namespace Database\Seeders;

use App\Domain\Agencies\Models\Agency;
use App\Domain\Bookings\Models\Booking;
use App\Domain\Bookings\Services\BookingService;
use App\Domain\Clients\Models\Client;
use App\Domain\Geo\Models\Destination;
use App\Domain\Offers\Models\Offer;
use App\Domain\Proposals\Models\Proposal;
use App\Domain\Requests\Models\LegService;
use App\Domain\Requests\Models\RequestLeg;
use App\Domain\Requests\Models\TravelRequest;
use App\Domain\RFQs\Models\Rfq;
use App\Domain\Settings\Models\Currency;
use App\Domain\Suppliers\Models\Supplier;
use App\Domain\Suppliers\Models\SupplierService;
use App\Domain\Users\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Demo seeder for the refactored schema (separate agencies/suppliers tables
 * with agency_users / supplier_users pivots).
 *
 * Currency model:
 *  - Each supplier works in their own currency; catalog + offers are stored in it.
 *  - Every offer/offer-item carries an AZN snapshot (exchange_rate + *_azn)
 *    computed at the rates below — the same data operators compare on.
 *
 * Run with: php artisan migrate:fresh --seed
 * All demo logins use the password "password".
 */
class DatabaseSeeder extends Seeder
{
    /** AZN per 1 unit of currency. */
    private array $rates = [
        'AZN' => 1.000000,
        'USD' => 1.700000,
        'EUR' => 1.890000,
        'KZT' => 0.003600,
        'UZS' => 0.000130,
    ];

    private function azn(float $amount, string $currency): float
    {
        return round($amount * ($this->rates[$currency] ?? 1.0), 2);
    }

    public function run(): void
    {
        DB::transaction(fn () => $this->seed());
    }

    private function seed(): void
    {
        // =====================================================================
        // Currencies (AZN base) + markup defaults
        // =====================================================================
        $currencies = [
            ['code' => 'AZN', 'name' => 'Манат',            'rate' => 1.000000, 'is_active' => true,  'is_default' => true],
            ['code' => 'USD', 'name' => 'Доллар США',        'rate' => 1.700000, 'is_active' => true,  'is_default' => false],
            ['code' => 'EUR', 'name' => 'Евро',              'rate' => 1.890000, 'is_active' => true,  'is_default' => false],
            ['code' => 'KZT', 'name' => 'Казахстанский тенге', 'rate' => 0.003600, 'is_active' => true,  'is_default' => false],
            ['code' => 'UZS', 'name' => 'Узбекский сум',     'rate' => 0.000130, 'is_active' => true,  'is_default' => false],
            ['code' => 'RUB', 'name' => 'Российский рубль',   'rate' => 0.018500, 'is_active' => false, 'is_default' => false],
            ['code' => 'TRY', 'name' => 'Турецкая лира',     'rate' => 0.049000, 'is_active' => false, 'is_default' => false],
        ];
        foreach ($currencies as $c) {
            Currency::updateOrCreate(['code' => $c['code']], $c + ['rates_updated_at' => now()]);
        }

        // Справочник услуг (типы + атрибуты + наценки по умолчанию) — динамическая
        // замена enum'ов; наценка живёт в service_types.default_markup_pct.
        $this->call(ServiceCatalogSeeder::class);

        // Справочник стран региона (партнёры + направления). Нужен до заявок,
        // т.к. legs/destinations ссылаются на countries по коду.
        $this->call(CountrySeeder::class);

        // Демо-заявки заведены по старой одностраничной модели (destination строкой).
        // Достраиваем мультистрановую: один сегмент (все группы едут в Азербайджан) со
        // своими датами/городами/услугами — из этого живёт RFQ по паре (сегмент×услуга)
        // и публичная token-страница поставщика. Идемпотентно: повторный вызов вернёт leg.
        $azDestinations = Destination::where('country_code', 'AZ')->pluck('id', 'name'); // англ-имя => id
        $cityRuToEn = [
            'Баку' => 'Baku', 'Шеки' => 'Sheki', 'Габала' => 'Gabala',
            'Гянджа' => 'Ganja', 'Куба' => 'Quba', 'Ланкаран' => 'Lankaran',
        ];

        $makeLeg = function (TravelRequest $r) use ($azDestinations, $cityRuToEn): RequestLeg {
            if ($existing = RequestLeg::where('travel_request_id', $r->id)->first()) {
                return $existing;
            }

            $leg = RequestLeg::create([
                'travel_request_id' => $r->id,
                'country_code'      => 'AZ',
                'date_from'         => $r->travel_date_from,
                'date_to'           => $r->travel_date_to,
                'sort_order'        => 1,
            ]);

            // Города: маппим из строки destination в справочник; что не нашли — пропускаем.
            $order = 1;
            foreach (array_filter(array_map('trim', explode(',', (string) $r->destination))) as $ru) {
                $en = $cityRuToEn[$ru] ?? null;
                if ($en !== null && isset($azDestinations[$en])) {
                    $leg->destinations()->attach($azDestinations[$en], ['sort_order' => $order++]);
                }
            }
            if ($order === 1 && isset($azDestinations['Baku'])) {
                $leg->destinations()->attach($azDestinations['Baku'], ['sort_order' => 1]); // фолбэк
            }

            // Услуги сегмента из services_needed заявки (requirements пока пустые).
            foreach ((array) ($r->services_needed ?? []) as $serviceType) {
                $leg->services()->create(['service_type' => $serviceType, 'requirements' => []]);
            }

            return $leg;
        };

        // =====================================================================
        // Operator
        // =====================================================================
        $operator = User::create([
            'name'         => 'Руслан (оператор)',
            'email'        => 'dev@caspirex.com',
            'password'     => Hash::make('password'),
            'role'         => 'operator',
            'company_name' => 'Caspirex DMC',
            'phone'        => '+994 12 498 55 00',
            'country'      => 'AZ',
            'currency_code' => 'AZN',
        ]);

        // =====================================================================
        // Agencies (+ owner user linked via agency_users)
        // =====================================================================
        $makeAgency = function (string $name, string $email, string $country, string $currency, string $ownerName, string $phone): Agency {
            $agency = Agency::create([
                'name'          => $name,
                'email'         => $email,
                'phone'         => $phone,
                'country'       => $country,
                'currency_code' => $currency,
            ]);

            $owner = User::create([
                'name'          => $ownerName,
                'email'         => $email,
                'password'      => Hash::make('password'),
                'role'          => 'agency',
                'company_name'  => $name,
                'phone'         => $phone,
                'country'       => $country,
                'currency_code' => $currency,
            ]);

            $agency->users()->attach($owner->id, ['role' => 'owner']);

            return $agency;
        };

        $nomad    = $makeAgency('Nomad Travel LLP', 'groups@nomadtravel.kz', 'KZ', 'KZT', 'Nomad Travel Almaty', '+7 727 344 55 66');
        $asia     = $makeAgency('AsiaTours Kazakhstan ТОО', 'booking@asiatours.kz', 'KZ', 'KZT', 'AsiaTours Astana', '+7 717 272 10 20');
        $samarkand = $makeAgency('Samarkand Express MChJ', 'info@samarkandexpress.uz', 'UZ', 'UZS', 'Samarkand Express', '+998 66 234 56 78');
        $fergana  = $makeAgency('Fergana Tourist Group MChJ', 'tours@ferganatg.uz', 'UZ', 'UZS', 'Fergana Tourist Group', '+998 73 244 11 22');

        // =====================================================================
        // Suppliers (+ owner user, + catalog in the supplier's own currency)
        // =====================================================================
        $makeSupplier = function (string $name, string $email, array $types, string $currency, string $desc, string $website, string $phone): Supplier {
            $supplier = Supplier::create([
                'name'          => $name,
                'email'         => $email,
                'phone'         => $phone,
                'country'       => 'AZ',
                'currency_code' => $currency,
                'service_types' => $types,
                'description'   => $desc,
                'website'       => $website,
                'is_active'     => true,
                'uses_portal'   => true,
            ]);

            $owner = User::create([
                'name'          => $name,
                'email'         => $email,
                'password'      => Hash::make('password'),
                'role'          => 'supplier',
                'company_name'  => $name,
                'phone'         => $phone,
                'country'       => 'AZ',
                'currency_code' => $currency,
            ]);

            $supplier->users()->attach($owner->id, ['role' => 'owner']);

            return $supplier;
        };

        // currency mix on purpose: USD / AZN / EUR — to exercise conversion
        $fairmont = $makeSupplier('Fairmont Baku', 'groups@fairmont-baku.az', ['accommodation'], 'USD', 'Пятизвёздочный отель в Пламенных башнях с видом на Каспий.', 'https://www.fairmont.com/baku', '+994 12 565 88 88');
        $shah     = $makeSupplier('Shah Palace Hotel', 'reservations@shahpalace.az', ['accommodation'], 'USD', 'Бутик-отель 5★ в Ичери-шехер.', 'https://www.shahpalacehotel.az', '+994 12 493 77 55');
        $qafqaz   = $makeSupplier('Qafqaz Resort Hotel', 'sales@qafqazresort.az', ['accommodation'], 'AZN', 'Горный курорт 4★ в Габале.', 'https://www.qafqazresort.az', '+994 22 256 00 01');
        $bakuTransfer = $makeSupplier('Baku City Transfer MMC', 'orders@bakutransfer.az', ['transport'], 'AZN', 'Трансферы и перевозки по Азербайджану.', 'https://www.bakutransfer.az', '+994 50 222 33 44');
        $caspian  = $makeSupplier('Caspian Shuttle MMC', 'booking@caspianshuttle.az', ['transport'], 'USD', 'Трансферы аэропорт ↔ Баку и экскурсии.', 'https://www.caspianshuttle.az', '+994 51 300 77 88');
        $bakuGuide = $makeSupplier('Baku Guide Service MMC', 'info@bakuguide.az', ['guide', 'activity'], 'AZN', 'Лицензированные гиды (RU/KZ/UZ/EN).', 'https://www.bakuguide.az', '+994 55 411 22 33');
        $caucasus = $makeSupplier('Caucasus Explore Tours', 'hello@caucasusexplore.az', ['guide', 'activity'], 'EUR', 'Экскурсии по регионам Азербайджана.', 'https://www.caucasusexplore.az', '+994 55 533 44 11');

        $svc = function (Supplier $s, string $type, string $name, ?int $capacity, float $price, string $unit, string $desc = ''): void {
            SupplierService::create([
                'supplier_id'  => $s->id,
                'type'         => $type,
                'name'         => $name,
                'description'  => $desc,
                'capacity'     => $capacity,
                'base_price'   => $price,
                'currency'     => $s->currency_code, // reference price always in supplier currency
                'price_unit'   => $unit,
                'is_available' => true,
            ]);
        };

        $svc($fairmont, 'accommodation', 'Deluxe Room Caspian View', 2, 310, 'per_night', 'Делюкс с видом на море, завтрак включён.');
        $svc($fairmont, 'accommodation', 'Signature Suite', 2, 590, 'per_night', 'Люкс с панорамой Пламенных башен.');
        $svc($shah, 'accommodation', 'Superior Room Old City', 2, 280, 'per_night', 'Вид на крепостную стену Ичери-шехер.');
        $svc($shah, 'other', 'Групповой ужин (азерб. кухня)', null, 45, 'per_person', 'Тематический ужин для групп от 8 чел.');
        $svc($qafqaz, 'accommodation', 'Standard Room (Mountain View)', 2, 270, 'per_night', 'Стандарт с видом на горы, завтрак включён.');
        $svc($qafqaz, 'accommodation', 'Group Rate -20% (15+ pax)', 2, 220, 'per_night', 'Групповой тариф HB.');
        $svc($bakuTransfer, 'transport', 'Mercedes Sprinter 19 мест', 19, 320, 'per_day', 'Минибус с кондиционером и водителем.');
        $svc($bakuTransfer, 'transport', 'Туристический автобус 50 мест', 50, 595, 'per_day', 'Автобус для больших групп.');
        $svc($caspian, 'transport', 'Ford Transit 16 мест', 16, 160, 'per_day', 'Минибус, опытный водитель.');
        $svc($bakuGuide, 'guide', 'Русскоязычный гид, Баку', null, 255, 'per_day', 'Лицензированный гид по Баку.');
        $svc($bakuGuide, 'activity', 'Экскурсия по Старому городу', 30, 1190, 'per_group', 'Ичери-шехер, ~5 часов.');
        $svc($caucasus, 'guide', 'Узбекоязычный гид, Шеки/Лагич', null, 90, 'per_day', 'Гид на узбекском языке.');
        $svc($caucasus, 'activity', 'Шеки + Лагич, 2 дня (группа)', 25, 580, 'per_group', 'Двухдневный маршрут без проживания.');

        // =====================================================================
        // Clients
        // =====================================================================
        $client = fn (Agency $a, string $name, string $email, string $nat, string $dob, ?string $notes = null): Client => Client::create([
            'agency_id'       => $a->id,
            'name'            => $name,
            'email'           => $email,
            'phone'           => '+0000000000',
            'nationality'     => $nat,
            'date_of_birth'   => $dob,
            'passport_number' => strtoupper(Str::random(2)).random_int(1000000, 9999999),
            'notes'           => $notes,
        ]);

        $nurlan = $client($nomad, 'Нурлан Сейткали', 'n.seitkali@nomadtravel.kz', 'KZ', '1982-04-15', 'Руководитель группы, VIP. Халяльное питание.');
        $aigerim = $client($nomad, 'Айгерим Бекова', 'a.bekova@nomadtravel.kz', 'KZ', '1990-08-20');
        $marat = $client($nomad, 'Марат Джумагалиев', 'm.dzhumagaliev@nomadtravel.kz', 'KZ', '1977-12-03');
        $daniyar = $client($asia, 'Данияр Ахметов', 'd.akhmetov@asiatours.kz', 'KZ', '1968-05-10', 'Глава делегации. Протокольная встреча.');
        $zulfiya = $client($asia, 'Зульфия Касымова', 'z.kassymova@asiatours.kz', 'KZ', '1985-02-28');
        $bobur = $client($samarkand, 'Бобур Каримов', 'b.karimov@samarkandexpress.uz', 'UZ', '1979-09-14', 'Группа паломников.');
        $client($fergana, 'Дилноза Юсупова', 'd.yusupova@ferganatg.uz', 'UZ', '1994-03-07');

        // =====================================================================
        // Travel requests
        // =====================================================================
        $req1 = TravelRequest::create([
            'agency_id'        => $nomad->id,
            'title'            => 'Баку — Шеки — Габала, группа 18 чел.',
            'destination'      => 'Баку, Шеки, Габала',
            'travel_date_from' => '2026-07-12',
            'travel_date_to'   => '2026-07-22',
            'pax_count'        => 18,
            'services_needed'  => ['accommodation', 'transport', 'guide'],
            'notes'            => 'Казахстанская группа. Гид RU/KZ. Отели 4★+. Халяльное питание.',
            'status'           => 'processing',
        ]);
        DB::table('travel_request_client')->insert([
            ['travel_request_id' => $req1->id, 'client_id' => $nurlan->id, 'is_lead' => true],
            ['travel_request_id' => $req1->id, 'client_id' => $aigerim->id, 'is_lead' => false],
            ['travel_request_id' => $req1->id, 'client_id' => $marat->id, 'is_lead' => false],
        ]);

        $req2 = TravelRequest::create([
            'agency_id'        => $asia->id,
            'title'            => 'VIP деловая делегация — Баку',
            'destination'      => 'Баку',
            'travel_date_from' => '2026-06-22',
            'travel_date_to'   => '2026-06-25',
            'pax_count'        => 5,
            'services_needed'  => ['accommodation', 'transport'],
            'notes'            => 'VIP из Казахстана. Проживание 5★, авто бизнес-класса.',
            'status'           => 'processing',
        ]);
        DB::table('travel_request_client')->insert([
            ['travel_request_id' => $req2->id, 'client_id' => $daniyar->id, 'is_lead' => true],
            ['travel_request_id' => $req2->id, 'client_id' => $zulfiya->id, 'is_lead' => false],
        ]);

        $req3 = TravelRequest::create([
            'agency_id'        => $samarkand->id,
            'title'            => 'Баку: Старый город и Абшерон — 12 чел.',
            'destination'      => 'Баку, Ичери-шехер, Гобустан',
            'travel_date_from' => '2026-08-05',
            'travel_date_to'   => '2026-08-09',
            'pax_count'        => 12,
            'services_needed'  => ['accommodation', 'transport', 'guide'],
            'notes'            => 'Узбекская группа. Гид UZ/RU. Отели 3★–4★.',
            'status'           => 'submitted',
        ]);
        DB::table('travel_request_client')->insert([
            ['travel_request_id' => $req3->id, 'client_id' => $bobur->id, 'is_lead' => true],
        ]);

        TravelRequest::create([
            'agency_id'        => $fergana->id,
            'title'            => 'Природный тур Азербайджан — 24 чел.',
            'destination'      => 'Баку, Гобустан, Шеки',
            'travel_date_from' => '2026-09-10',
            'travel_date_to'   => '2026-09-18',
            'pax_count'        => 24,
            'services_needed'  => ['accommodation', 'transport', 'guide', 'activity'],
            'notes'            => 'Эконом-бюджет, отели 3★. Черновик.',
            'status'           => 'draft',
        ]);

        // Booked request (full chain → booking)
        $req5 = TravelRequest::create([
            'agency_id'        => $nomad->id,
            'title'            => 'Уикенд в Баку — 8 чел.',
            'destination'      => 'Баку',
            'travel_date_from' => '2026-06-12',
            'travel_date_to'   => '2026-06-15',
            'pax_count'        => 8,
            'services_needed'  => ['transport', 'guide'],
            'notes'            => 'Короткий тур, подтверждён.',
            'status'           => 'booked',
        ]);

        // =====================================================================
        // RFQs + supplier pivots (with portal tokens)
        // =====================================================================
        $sentAt = now()->subDays(2);

        $rfq = function (TravelRequest $r, string $type, string $title, string $status, string $deadline, array $suppliers) use ($operator, $makeLeg): Rfq {
            // RFQ привязан к сегменту заявки (страна + leg): поставщик матчится по стране,
            // а token-страница тянет даты/города/требования именно из этого leg.
            $leg = $makeLeg($r);

            $rfq = Rfq::create([
                'request_id'   => $r->id,
                'leg_id'       => $leg->id,
                'country_code' => $leg->country_code,
                'operator_id'  => $operator->id,
                'title'        => $title,
                'description'  => $title,
                'service_type' => $type,
                'deadline_at'  => $deadline,
                'status'       => $status,
            ]);

            $rows = [];
            foreach ($suppliers as $s) {
                $rows[] = [
                    'rfq_id'           => $rfq->id,
                    'supplier_id'      => $s->id,
                    'sent_at'          => now()->subDays(2),
                    'token'            => Str::random(64),
                    'token_expires_at' => Carbon::parse($deadline)->addDays(3),
                    'service_types'    => json_encode([$type]),
                    'notes'            => null,
                ];
            }
            DB::table('rfq_supplier')->insert($rows);

            return $rfq;
        };

        $rfq1a = $rfq($req1, 'accommodation', 'Проживание — Баку+Шеки+Габала, 18 pax', 'awaiting', '2026-06-25', [$fairmont, $qafqaz]);
        $rfq1b = $rfq($req1, 'transport', 'Трансфер — маршрут 10 дней, 18 pax', 'awaiting', '2026-06-25', [$bakuTransfer, $caspian]);
        $rfq1c = $rfq($req1, 'guide', 'Гид — Баку+регионы, 18 pax', 'sent', '2026-06-25', [$bakuGuide, $caucasus]);
        $rfq2a = $rfq($req2, 'accommodation', 'Проживание VIP — Баку, 5 pax', 'awaiting', '2026-06-12', [$fairmont, $shah]);
        $rfq2b = $rfq($req2, 'transport', 'VIP трансфер — Баку, 5 pax', 'sent', '2026-06-12', [$bakuTransfer]);
        $rfq5a = $rfq($req5, 'transport', 'Трансфер — уикенд Баку, 8 pax', 'closed', '2026-05-20', [$caspian]);
        $rfq5b = $rfq($req5, 'guide', 'Гид — уикенд Баку, 8 pax', 'closed', '2026-05-20', [$bakuGuide]);

        // =====================================================================
        // Offers (in supplier currency, with AZN snapshot)
        // =====================================================================
        $offer = function (Rfq $rfq, Supplier $s, array $items, string $status, string $validUntil, ?string $notes, bool $isPartial = false, array $covered = [], ?array $uncovered = null): Offer {
            $cur  = $s->currency_code;
            $rate = $this->rates[$cur] ?? 1.0;
            $total = array_sum(array_column($items, 'price'));

            $offer = Offer::create([
                'rfq_id'             => $rfq->id,
                'supplier_id'        => $s->id,
                'is_partial'         => $isPartial,
                'covered_services'   => $covered ?: [$rfq->service_type],
                'uncovered_services' => $uncovered,
                'unit_price'         => $total,
                'currency'           => $cur,
                'exchange_rate'      => $rate,
                'unit_price_azn'     => $this->azn($total, $cur),
                'valid_until'        => $validUntil,
                'notes'              => $notes,
                'status'             => $status,
            ]);

            foreach ($items as $it) {
                $offer->items()->create([
                    'type'           => $it['type'],
                    'name'           => $it['name'],
                    'quantity'       => 1,
                    'unit_price'     => $it['price'],
                    'currency'       => $cur,
                    'unit_price_azn' => $this->azn($it['price'], $cur),
                    'exchange_rate'  => $rate,
                    'price_unit'     => 'fixed',
                ]);
            }

            return $offer;
        };

        $oFairmont1 = $offer($rfq1a, $fairmont, [['type' => 'accommodation', 'name' => 'Баку 4 ночи, 10 номеров', 'price' => 2850]], 'reviewed', '2026-07-05', 'Групповой тариф, халяльное меню по запросу. Покрывает только Баку (9 Deluxe + 1 Suite, 4 ночи); Шеки и Габала — вне зоны Fairmont.');
        $oQafqaz1   = $offer($rfq1a, $qafqaz, [['type' => 'accommodation', 'name' => 'Габала+Шеки 6 ночей', 'price' => 3960]], 'received', '2026-07-05', 'Групповой тариф -20%, HB. Габала 4н + Шеки 2н; Баку — вне региона.');
        $oTransfer1 = $offer($rfq1b, $bakuTransfer, [['type' => 'transport', 'name' => 'Sprinter, 10 дней', 'price' => 3200]], 'reviewed', '2026-07-05', 'Включены топливо, парковки, водитель.');
        $offer($rfq1b, $caspian, [['type' => 'transport', 'name' => 'Ford Transit, 10 дней', 'price' => 1700]], 'received', '2026-07-05', 'Альтернатива — Transit 16 мест.');
        $offer($rfq1c, $bakuGuide, [['type' => 'guide', 'name' => 'Гид RU, 10 дней', 'price' => 2550]], 'received', '2026-07-05', 'Русскоязычный гид.');
        $offer($rfq2a, $fairmont, [['type' => 'accommodation', 'name' => '3 Suite + 2 Deluxe, 3 ночи', 'price' => 2340]], 'received', '2026-06-18', 'Консьерж 24/7, VIP-приветствие.');
        $offer($rfq2b, $bakuTransfer, [['type' => 'transport', 'name' => '2× Mercedes S-Class, 3 дня', 'price' => 1170]], 'received', '2026-06-18', 'Водитель со знанием KZ по запросу.');

        // Booked-chain offers (selected)
        $oCaspian5 = $offer($rfq5a, $caspian, [['type' => 'transport', 'name' => 'Трансферы уикенд', 'price' => 280]], 'selected', '2026-06-01', 'Встреча в аэропорту.');
        $oGuide5   = $offer($rfq5b, $bakuGuide, [['type' => 'guide', 'name' => 'Гид 2 дня', 'price' => 360]], 'selected', '2026-06-01', 'Обзорные экскурсии по Баку.');

        // =====================================================================
        // Proposals
        // =====================================================================
        // Draft proposal for request 1 (operator builds in AZN)
        $p1AznTotal = 0.0;
        $p1Lines = [
            [$oFairmont1, 18.00, 'Fairmont — узнаваемый бренд для первого впечатления.'],
            [$oQafqaz1,   18.00, 'Qafqaz даёт HB в регионах.'],
            [$oTransfer1, 22.00, 'Sprinter на весь маршрут.'],
        ];
        foreach ($p1Lines as [$o, $pct, $note]) {
            $p1AznTotal += round($o->unit_price_azn * (1 + $pct / 100), 2);
        }

        $proposal1 = Proposal::create([
            'request_id'  => $req1->id,
            'operator_id' => $operator->id,
            'title'       => 'КП — Баку–Шеки–Габала, 18 pax',
            'description' => 'Fairmont (Баку) + Qafqaz (Габала/Шеки) + Sprinter. Все услуги покрыты.',
            'total_price' => $p1AznTotal,
            'currency'    => 'AZN',
            'valid_until' => '2026-07-08',
            'status'      => 'draft',
        ]);
        foreach ($p1Lines as [$o, $pct, $note]) {
            DB::table('proposal_offer')->insert([
                'proposal_id'          => $proposal1->id,
                'offer_id'             => $o->id,
                'operator_notes'       => $note,
                'markup_pct'           => $pct,
                'agency_currency_code' => $nomad->currency_code,
                'agency_exchange_rate' => $this->rates[$nomad->currency_code],
            ]);
        }

        // Accepted proposal for request 5 → booking (snapshot to agency currency)
        $p5AznTotal = 0.0;
        $p5Lines = [[$oCaspian5, 22.00, 'Трансферы.'], [$oGuide5, 15.00, 'Гид.']];
        foreach ($p5Lines as [$o, $pct, $note]) {
            $p5AznTotal += round($o->unit_price_azn * (1 + $pct / 100), 2);
        }
        $agencyRate = $this->rates[$nomad->currency_code];           // AZN per 1 KZT
        $p5AgencyTotal = round($p5AznTotal / $agencyRate, 2);        // AZN → agency currency

        $proposal5 = Proposal::create([
            'request_id'             => $req5->id,
            'operator_id'            => $operator->id,
            'title'                  => 'КП — Уикенд в Баку, 8 pax',
            'description'            => 'Трансферы + гид на 2 дня.',
            'total_price'            => $p5AgencyTotal,
            'currency'               => $nomad->currency_code,
            'original_total_price'   => $p5AznTotal,
            'original_currency'      => 'AZN',
            'exchange_rate_snapshot' => $agencyRate,
            'valid_until'            => '2026-06-05',
            'status'                 => 'accepted',
        ]);
        foreach ($p5Lines as [$o, $pct, $note]) {
            DB::table('proposal_offer')->insert([
                'proposal_id'          => $proposal5->id,
                'offer_id'             => $o->id,
                'operator_notes'       => $note,
                'markup_pct'           => $pct,
                'agency_currency_code' => $nomad->currency_code,
                'agency_exchange_rate' => $agencyRate,
            ]);
        }

        // =====================================================================
        // Booking (from accepted proposal 5) — через сервис: замораживает снапшот
        // себестоимости (booking_items + cost/sell/margin AZN) для отчётов и маржи.
        // =====================================================================
        $booking5 = app(BookingService::class)->createFromProposal($proposal5);
        $booking5->confirmed_at = now();   // подтверждено сегодня → видно в KPI «сегодня/неделя/месяц»
        $booking5->save();

        // =====================================================================
        // ВОЛНА 2 — реалистичное наполнение: все статусы, разные сроки, вложения
        // =====================================================================
        $own   = fn ($e) => $e->users()->first();                 // владелец агентства/поставщика
        $dPast = fn (int $d) => Carbon::today()->subDays($d)->toDateString();
        $dFut  = fn (int $d) => Carbon::today()->addDays($d)->toDateString();

        // 1×1 прозрачный PNG (валидный, открывается)
        $pngBytes = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk'
            .'+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='
        );

        // Минимальный валидный PDF с корректным xref — реально скачивается/открывается
        $makePdf = function (string $text): string {
            $stream = "BT /F1 16 Tf 24 60 Td ({$text}) Tj ET";
            $objs = [
                '<< /Type /Catalog /Pages 2 0 R >>',
                '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
                '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 360 120] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>',
                '<< /Length '.strlen($stream)." >>\nstream\n{$stream}\nendstream",
                '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
            ];
            $pdf = "%PDF-1.4\n";
            $offsets = [];
            foreach ($objs as $i => $body) {
                $offsets[$i + 1] = strlen($pdf);
                $pdf .= ($i + 1)." 0 obj\n".$body."\nendobj\n";
            }
            $xref = strlen($pdf);
            $n = count($objs) + 1;
            $pdf .= "xref\n0 {$n}\n0000000000 65535 f \n";
            for ($i = 1; $i < $n; $i++) {
                $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
            }
            $pdf .= "trailer\n<< /Size {$n} /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";

            return $pdf;
        };
        $pdf = fn (string $name, string $text) => [$name, 'application/pdf', $makePdf($text)];
        $png = fn (string $name) => [$name, 'image/png', $pngBytes];

        // Кладёт файл на диск local и создаёт запись вложения
        $attach = function ($model, $uploader, string $type, array $file): void {
            [$filename, $mime, $bytes] = $file;
            $path = "attachments/{$type}/{$model->id}/".Str::random(24).'-'.$filename;
            Storage::disk('local')->put($path, $bytes);
            $model->attachments()->create([
                'uploader_id' => $uploader->id,
                'disk'        => 'local',
                'path'        => $path,
                'filename'    => $filename,
                'mime_type'   => $mime,
                'size'        => strlen($bytes),
            ]);
        };

        // Proposal в валюте агентства со снимком курса (как proposal5)
        $makeProposal = function (TravelRequest $r, Agency $a, string $title, string $desc, string $status, string $validUntil, array $lines) use ($operator): Proposal {
            $aznTotal = 0.0;
            foreach ($lines as [$o, $pct, $note]) {
                $aznTotal += round($o->unit_price_azn * (1 + $pct / 100), 2);
            }
            $rate = $this->rates[$a->currency_code] ?? 1.0;
            $p = Proposal::create([
                'request_id'             => $r->id,
                'operator_id'            => $operator->id,
                'title'                  => $title,
                'description'            => $desc,
                'total_price'            => round($aznTotal / $rate, 2),
                'currency'               => $a->currency_code,
                'original_total_price'   => $aznTotal,
                'original_currency'      => 'AZN',
                'exchange_rate_snapshot' => $rate,
                'valid_until'            => $validUntil,
                'status'                 => $status,
            ]);
            foreach ($lines as [$o, $pct, $note]) {
                DB::table('proposal_offer')->insert([
                    'proposal_id'          => $p->id,
                    'offer_id'             => $o->id,
                    'operator_notes'       => $note,
                    'markup_pct'           => $pct,
                    'agency_currency_code' => $a->currency_code,
                    'agency_exchange_rate' => $rate,
                ]);
            }

            return $p;
        };

        // Через сервис: снапшот себестоимости + booking_items, затем нужный статус/дата.
        $makeBooking = function (Proposal $p, string $status, $confirmedAt): void {
            $booking = app(BookingService::class)->createFromProposal($p);
            $booking->status = $status;
            $booking->confirmed_at = $confirmedAt;
            $booking->save();
            // createFromProposal переводит заявку в «booked»; завершённый тур → «completed».
            if ($status === 'completed') {
                $p->request->update(['status' => 'completed']);
            }
        };

        // --- Вложения на сущности волны 1 (в т.ч. read-only у выбранного оффера) -
        $attach($req1, $own($nomad), 'requests', $pdf('Программа_тура.pdf', 'Tour program - Baku Sheki Gabala'));
        $attach($req1, $own($nomad), 'requests', $pdf('Список_группы.pdf', 'Group list 18 pax'));
        $attach($rfq1a, $operator, 'rfqs', $pdf('Требования_к_размещению.pdf', 'Accommodation requirements'));
        $attach($oFairmont1, $own($fairmont), 'offers', $pdf('КП_Fairmont.pdf', 'Fairmont quotation'));
        $attach($oFairmont1, $own($fairmont), 'offers', $png('Фото_номера.png'));
        $attach($oTransfer1, $own($bakuTransfer), 'offers', $pdf('Маршрут_трансфера.pdf', 'Transfer route 10 days'));
        $attach($proposal1, $operator, 'proposals', $pdf('Смета_КП.pdf', 'Cost estimate'));
        $attach($oGuide5, $own($bakuGuide), 'offers', $pdf('Ваучер_гид.pdf', 'Guide voucher'));        // оффер 1009 — read-only
        $attach($oCaspian5, $own($caspian), 'offers', $pdf('Подтверждение_трансфер.pdf', 'Transfer confirmation'));

        // --- req8: свежая заявка (submitted), ещё без запросов --------------------
        $req8 = TravelRequest::create([
            'agency_id'        => $samarkand->id,
            'title'            => 'Новый запрос — Баку, 3 дня, 10 чел.',
            'destination'      => 'Баку',
            'travel_date_from' => $dFut(40),
            'travel_date_to'   => $dFut(43),
            'pax_count'        => 10,
            'services_needed'  => ['accommodation', 'guide'],
            'notes'            => 'Только поступила, оператор ещё не обработал.',
            'status'           => 'submitted',
        ]);
        $attach($req8, $own($samarkand), 'requests', $pdf('Пожелания_клиента.pdf', 'Client wishes'));

        // --- req7: отменённая заявка (cancelled) + отозванный оффер ---------------
        $req7 = TravelRequest::create([
            'agency_id'        => $asia->id,
            'title'            => 'Габала, 14 чел. (отменён клиентом)',
            'destination'      => 'Габала',
            'travel_date_from' => $dFut(18),
            'travel_date_to'   => $dFut(22),
            'pax_count'        => 14,
            'services_needed'  => ['accommodation'],
            'notes'            => 'Клиент отменил поездку.',
            'status'           => 'cancelled',
        ]);
        $rfq7 = $rfq($req7, 'accommodation', 'Проживание — Габала (отменён)', 'cancelled', $dFut(5), [$qafqaz]);
        $offer($rfq7, $qafqaz, [['type' => 'accommodation', 'name' => 'Габала 3 ночи', 'price' => 1200]], 'withdrawn', $dFut(10), 'Отозвано — тур отменён.');

        // --- req9: processing, КП ОТПРАВЛЕНО (sent) — ждёт решения агентства ------
        $req9 = TravelRequest::create([
            'agency_id'        => $fergana->id,
            'title'            => 'Эко-тур Шеки–Лагич, 16 чел.',
            'destination'      => 'Шеки, Лагич',
            'travel_date_from' => $dFut(30),
            'travel_date_to'   => $dFut(36),
            'pax_count'        => 16,
            'services_needed'  => ['accommodation', 'guide', 'activity'],
            'notes'            => 'Гид UZ/RU. Эко-маршрут.',
            'status'           => 'processing',
        ]);
        $rfq9g  = $rfq($req9, 'guide', 'Гид — Шеки/Лагич, 16 pax', 'awaiting', $dFut(2), [$bakuGuide, $caucasus]);     // 🔥 горящий дедлайн
        $o9g    = $offer($rfq9g, $bakuGuide, [['type' => 'guide', 'name' => 'Гид RU/UZ, 3 дня', 'price' => 720]], 'reviewed', $dFut(2), 'Двуязычный гид.'); // valid истекает скоро
        $offer($rfq9g, $caucasus, [['type' => 'guide', 'name' => 'Гид UZ, 3 дня', 'price' => 270]], 'withdrawn', $dFut(8), 'Отозвано поставщиком.');
        $rfq9a  = $rfq($req9, 'activity', 'Активности — Шеки+Лагич', 'awaiting', $dFut(8), [$bakuGuide, $caucasus]);
        $o9a    = $offer($rfq9a, $caucasus, [['type' => 'activity', 'name' => 'Шеки+Лагич 2 дня', 'price' => 540]], 'received', $dFut(10), 'Двухдневная программа.');
        $offer($rfq9a, $bakuGuide, [['type' => 'activity', 'name' => 'Экскурсии', 'price' => 1190]], 'expired', $dPast(2), 'Срок действия истёк.'); // expired
        $rfq9acc = $rfq($req9, 'accommodation', 'Проживание — Шеки', 'sent', $dFut(12), [$qafqaz]);
        $offer($rfq9acc, $qafqaz, [['type' => 'accommodation', 'name' => 'Шеки 3 ночи', 'price' => 1680]], 'received', $dFut(12), null);
        $rfq($req9, 'transport', 'Трансфер — черновик (не разослан)', 'draft', $dFut(12), []);                       // draft RFQ без поставщиков
        $p9 = $makeProposal($req9, $fergana, 'КП — Эко-тур Шеки/Лагич, 16 pax', 'Гид + активности. На рассмотрении агентства.', 'sent', $dFut(5), [[$o9g, 15.00, 'Гид.'], [$o9a, 20.00, 'Активности.']]);
        $attach($rfq9g, $operator, 'rfqs', $pdf('Бриф_по_гиду.pdf', 'Guide brief'));
        $attach($o9g, $own($bakuGuide), 'offers', $pdf('КП_гид.pdf', 'Guide quotation'));
        $attach($p9, $operator, 'proposals', $pdf('КП_Шеки_Лагич.pdf', 'Proposal Sheki Lagich'));

        // --- req10: processing, КП отклонено/просрочено/отозвано + rejected оффер -
        $req10 = TravelRequest::create([
            'agency_id'        => $asia->id,
            'title'            => 'Делегация — Баку 5★, 6 чел. (пересмотр КП)',
            'destination'      => 'Баку',
            'travel_date_from' => $dFut(20),
            'travel_date_to'   => $dFut(24),
            'pax_count'        => 6,
            'services_needed'  => ['accommodation', 'transport'],
            'notes'            => 'Несколько версий КП.',
            'status'           => 'processing',
        ]);
        $rfq10 = $rfq($req10, 'accommodation', 'Проживание — Баку 5★, 6 pax', 'awaiting', $dFut(6), [$fairmont, $shah]);
        $o10f  = $offer($rfq10, $fairmont, [['type' => 'accommodation', 'name' => '3 Suite, 4 ночи', 'price' => 2360]], 'reviewed', $dFut(7), 'Премиум размещение.');
        $offer($rfq10, $shah, [['type' => 'accommodation', 'name' => '4 номера, 4 ночи', 'price' => 1120]], 'rejected', $dFut(7), 'Не подошло по уровню.');
        $makeProposal($req10, $asia, 'КП — Делегация (отклонён)', 'Первый вариант, отклонён агентством.', 'rejected', $dFut(3), [[$o10f, 18.00, 'Fairmont.']]);
        $makeProposal($req10, $asia, 'КП — Делегация (просрочен)', 'Срок действия истёк.', 'expired', $dPast(3), [[$o10f, 16.00, 'Fairmont.']]);
        $makeProposal($req10, $asia, 'КП — Делегация (отозван)', 'Отменён оператором.', 'cancelled', $dFut(2), [[$o10f, 17.00, 'Fairmont.']]);

        // --- req6: ЗАВЕРШЁННЫЙ тур (completed) → booking completed -----------------
        $req6 = TravelRequest::create([
            'agency_id'        => $nomad->id,
            'title'            => 'Корпоратив Габала, 30 чел. (завершён)',
            'destination'      => 'Габала',
            'travel_date_from' => $dPast(50),
            'travel_date_to'   => $dPast(44),
            'pax_count'        => 30,
            'services_needed'  => ['accommodation', 'transport'],
            'notes'            => 'Завершённый корпоративный тур.',
            'status'           => 'completed',
        ]);
        $rfq6a = $rfq($req6, 'accommodation', 'Проживание — корпоратив 30 pax', 'closed', $dPast(60), [$qafqaz, $fairmont]);
        $o6q   = $offer($rfq6a, $qafqaz, [['type' => 'accommodation', 'name' => 'Габала 6 ночей, 15 номеров', 'price' => 6600]], 'selected', $dPast(55), 'Групповой тариф.');
        $offer($rfq6a, $fairmont, [['type' => 'accommodation', 'name' => 'Баку, 15 номеров', 'price' => 9300]], 'rejected', $dPast(55), 'Дороже бюджета.');
        $rfq6t = $rfq($req6, 'transport', 'Трансфер — корпоратив', 'closed', $dPast(60), [$bakuTransfer, $caspian]);
        $o6t   = $offer($rfq6t, $bakuTransfer, [['type' => 'transport', 'name' => 'Автобус 50 мест, 6 дней', 'price' => 3570]], 'selected', $dPast(55), 'Большой автобус.');
        $offer($rfq6t, $caspian, [['type' => 'transport', 'name' => '2× Transit', 'price' => 1920]], 'expired', $dPast(56), null);
        $p6 = $makeProposal($req6, $nomad, 'КП — Корпоратив Габала, 30 pax', 'Проживание + автобус. Принято и проведено.', 'accepted', $dPast(48), [[$o6q, 18.00, 'Отель.'], [$o6t, 22.00, 'Транспорт.']]);
        $makeBooking($p6, 'completed', Carbon::today()->subDays(58));
        $attach($o6q, $own($qafqaz), 'offers', $pdf('Договор_отель.pdf', 'Hotel contract'));

        // --- req11: тур В ПРОЦЕССЕ (booking in_progress, даты охватывают сегодня) --
        $req11 = TravelRequest::create([
            'agency_id'        => $nomad->id,
            'title'            => 'Тур в процессе — Баку, 12 чел.',
            'destination'      => 'Баку',
            'travel_date_from' => $dPast(3),
            'travel_date_to'   => $dFut(4),
            'pax_count'        => 12,
            'services_needed'  => ['transport', 'guide'],
            'notes'            => 'Тур идёт прямо сейчас.',
            'status'           => 'booked',
        ]);
        $rfq11t = $rfq($req11, 'transport', 'Трансфер — текущий тур', 'closed', $dPast(15), [$caspian]);
        $o11t   = $offer($rfq11t, $caspian, [['type' => 'transport', 'name' => 'Transit, 7 дней', 'price' => 1120]], 'selected', $dPast(12), null);
        $rfq11g = $rfq($req11, 'guide', 'Гид — текущий тур', 'closed', $dPast(15), [$bakuGuide]);
        $o11g   = $offer($rfq11g, $bakuGuide, [['type' => 'guide', 'name' => 'Гид RU, 7 дней', 'price' => 1785]], 'selected', $dPast(12), null);
        $p11 = $makeProposal($req11, $nomad, 'КП — Текущий тур, 12 pax', 'Транспорт + гид. В процессе.', 'accepted', $dPast(10), [[$o11t, 22.00, 'Транспорт.'], [$o11g, 15.00, 'Гид.']]);
        $makeBooking($p11, 'in_progress', Carbon::today()->subDays(8));

        // Заявки без RFQ (черновики и пр.) тоже получают сегмент — для единообразия,
        // чтобы вся витрина жила на мультистрановой модели, а не половина на legacy.
        TravelRequest::doesntHave('legs')->get()->each($makeLeg);
    }
}
