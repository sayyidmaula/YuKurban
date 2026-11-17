<?php

namespace Database\Seeders;

use App\Enums\AnimalStatus;
use App\Enums\AnimalType;
use App\Enums\LedgerType;
use App\Enums\OrderMethod;
use App\Enums\OrderStatus;
use App\Enums\PayoutStatus;
use App\Enums\Role;
use App\Enums\TicketDecision;
use App\Enums\TicketStatus;
use App\Enums\TicketType;
use App\Models\Animal;
use App\Models\AnimalMedia;
use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\ContactMessage;
use App\Models\FeeRule;
use App\Models\Order;
use App\Models\OrderEvidence;
use App\Models\OrderNote;
use App\Models\OrderStatusLog;
use App\Models\Payout;
use App\Models\PeternakProfile;
use App\Models\PriceTier;
use App\Models\Region;
use App\Models\SavingsPlan;
use App\Models\ServiceZone;
use App\Models\Setting;
use App\Models\SlaughterQuota;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use App\Services\LedgerService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $password = Hash::make('password');

        // Settings (BR-16, BR-05, BR-18)
        Setting::set('min_age_months', [
            'kambing' => 12,
            'domba' => 12,
            'sapi' => 24,
        ]);
        Setting::set('reservation_hours', 24);
        Setting::set('invoice_expiry_hours', 24);
        Setting::set('report_deadline_hours', 48);
        Setting::set('auto_confirm_hours', 48);
        Setting::set('idul_adha_date', '2027-06-16');
        Setting::set('purchase_window_sembelih_tim', [
            'open_at' => now()->subDays(2)->toIso8601String(),
            'close_at' => now()->addDays(30)->toIso8601String(),
        ]);
        Setting::set('purchase_window_kirim_hidup', [
            'open_at' => now()->subDays(2)->toIso8601String(),
            'close_at' => now()->addDays(25)->toIso8601String(),
        ]);

        // 1 Admin
        $admin = User::updateOrCreate(
            ['email' => 'admin@yukurban.test'],
            [
                'name' => 'Administrator YuKurban',
                'password' => $password,
                'role' => Role::ADMIN,
                'phone' => '08110000001',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // 5 Demo Regions & Service Zones (PRD Section 12, BR-07)
        $regions = [
            'bandung' => Region::updateOrCreate(['city' => 'Kota Bandung', 'province' => 'Jawa Barat'], ['is_active' => true]),
            'garut' => Region::updateOrCreate(['city' => 'Kabupaten Garut', 'province' => 'Jawa Barat'], ['is_active' => true]),
            'tasik' => Region::updateOrCreate(['city' => 'Kabupaten Tasikmalaya', 'province' => 'Jawa Barat'], ['is_active' => true]),
            'yogya' => Region::updateOrCreate(['city' => 'Kabupaten Sleman', 'province' => 'DI Yogyakarta'], ['is_active' => true]),
            'makassar' => Region::updateOrCreate(['city' => 'Kota Makassar', 'province' => 'Sulawesi Selatan'], ['is_active' => true]),
        ];

        ServiceZone::updateOrCreate(
            ['region_id' => $regions['bandung']->id],
            ['supports_sembelih_tim' => true, 'supports_kirim_hidup' => true, 'delivery_fee' => 150000, 'is_active' => true]
        );
        ServiceZone::updateOrCreate(
            ['region_id' => $regions['garut']->id],
            ['supports_sembelih_tim' => true, 'supports_kirim_hidup' => true, 'delivery_fee' => 100000, 'is_active' => true]
        );
        ServiceZone::updateOrCreate(
            ['region_id' => $regions['tasik']->id],
            ['supports_sembelih_tim' => true, 'supports_kirim_hidup' => false, 'delivery_fee' => 0, 'is_active' => true]
        );
        ServiceZone::updateOrCreate(
            ['region_id' => $regions['yogya']->id],
            ['supports_sembelih_tim' => false, 'supports_kirim_hidup' => true, 'delivery_fee' => 250000, 'is_active' => true]
        );
        ServiceZone::updateOrCreate(
            ['region_id' => $regions['makassar']->id],
            ['supports_sembelih_tim' => true, 'supports_kirim_hidup' => false, 'delivery_fee' => 0, 'is_active' => true]
        );

        // Fee Rules (PRD Section 7, BR-09)
        FeeRule::updateOrCreate(
            ['animal_type' => AnimalType::KAMBING],
            [
                'fee_slaughter' => 250000,
                'fee_distribution' => 150000,
                'commission_peternak' => 100000,
            ]
        );
        FeeRule::updateOrCreate(
            ['animal_type' => AnimalType::DOMBA],
            [
                'fee_slaughter' => 250000,
                'fee_distribution' => 150000,
                'commission_peternak' => 100000,
            ]
        );
        FeeRule::updateOrCreate(
            ['animal_type' => AnimalType::SAPI],
            [
                'fee_slaughter' => 1500000,
                'fee_distribution' => 800000,
                'commission_peternak' => 500000,
            ]
        );

        // Slaughter Quotas (PRD Section 7, BR-08)
        $quotaDates = [
            '2027-06-16' => 50,
            '2027-06-17' => 40,
            '2027-06-18' => 30,
            '2027-06-19' => 20,
            now()->format('Y-m-d') => 25,
            now()->addDay()->format('Y-m-d') => 25,
        ];
        foreach ($quotaDates as $date => $quota) {
            SlaughterQuota::updateOrCreate(
                ['date' => $date],
                ['quota' => $quota]
            );
        }

        // 3 Peternak (1 terverifikasi, 1 menunggu, 1 ditolak - PRD Section 12)
        $peternakUsers = [];
        for ($i = 1; $i <= 3; $i++) {
            $peternakUsers[] = User::updateOrCreate(
                ['email' => "peternak{$i}@yukurban.test"],
                [
                    'name' => "Mitra Peternak {$i}",
                    'password' => $password,
                    'role' => Role::PETERNAK,
                    'phone' => '0812000000' . $i,
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
        }

        // Profile 1: Terverifikasi
        $profile1 = PeternakProfile::updateOrCreate(
            ['user_id' => $peternakUsers[0]->id],
            [
                'farm_name' => 'Peternakan Garut Barokah',
                'address' => 'Jl. Samarang No. 45, Tarogong Kidul',
                'region_id' => $regions['garut']->id,
                'description' => 'Spesialis domba garut tanduk istimewa dan kambing etawa sehat bergaransi.',
                'status' => 'terverifikasi',
                'verified_by' => $admin->id,
                'verified_at' => now()->subDays(10),
            ]
        );

        // Profile 2: Menunggu
        $profile2 = PeternakProfile::updateOrCreate(
            ['user_id' => $peternakUsers[1]->id],
            [
                'farm_name' => 'Kandang Sapi Mandiri Tasik',
                'address' => 'Jl. Singaparna KM 11',
                'region_id' => $regions['tasik']->id,
                'description' => 'Penggemukan sapi limosin dan simental berkualitas.',
                'status' => 'menunggu',
                'verified_by' => null,
                'verified_at' => null,
            ]
        );

        // Profile 3: Ditolak
        $profile3 = PeternakProfile::updateOrCreate(
            ['user_id' => $peternakUsers[2]->id],
            [
                'farm_name' => 'Kandang Sleman Berdikari',
                'address' => 'Jl. Kaliurang KM 14',
                'region_id' => $regions['yogya']->id,
                'description' => 'Peternakan kambing jawa randu.',
                'status' => 'ditolak',
                'rejection_reason' => 'Foto kandang tidak mencakup fasilitas sanitasi air bersih yang memadai.',
                'verified_by' => $admin->id,
                'verified_at' => now()->subDays(5),
            ]
        );

        // 5 Penabung
        $penabungs = [];
        for ($i = 1; $i <= 5; $i++) {
            $penabungs[] = User::updateOrCreate(
                ['email' => "penabung{$i}@yukurban.test"],
                [
                    'name' => "Sdr. Penabung {$i}",
                    'password' => $password,
                    'role' => Role::PENABUNG,
                    'phone' => '0813000000' . $i,
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
        }

        // 3 Price Tiers (PRD Section 7 & 12)
        $tierEkonomis = PriceTier::updateOrCreate(
            ['name' => 'ekonomis'],
            [
                'min_price' => 2000000,
                'max_price' => 3500000,
                'sort_order' => 1,
            ]
        );

        $tierStandar = PriceTier::updateOrCreate(
            ['name' => 'standar'],
            [
                'min_price' => 3500000,
                'max_price' => 6000000,
                'sort_order' => 2,
            ]
        );

        $tierPremium = PriceTier::updateOrCreate(
            ['name' => 'premium'],
            [
                'min_price' => 6000000,
                'max_price' => 30000000,
                'sort_order' => 3,
            ]
        );

        // Savings Plans for 5 Penabung (PRD Section 12)
        $ledgerService = app(LedgerService::class);

        // Plan 1: Penabung 1
        $plan1 = SavingsPlan::updateOrCreate(
            ['user_id' => $penabungs[0]->id, 'name' => 'Tabungan Kurban Kambing Standar'],
            [
                'tier_id' => $tierStandar->id,
                'target_amount' => 5000000,
                'target_year' => (int) date('Y'),
                'monthly_amount' => 500000,
                'status' => 'menabung',
                'autodebet_enabled' => true,
                'autodebet_day' => 10,
                'balance_cached' => 0,
            ]
        );
        if ($plan1->ledgerEntries()->count() === 0) {
            $ledgerService->record($plan1, LedgerType::SETORAN, 1000000, 'Setoran perdana via VA BCA', $penabungs[0]);
            $ledgerService->record($plan1, LedgerType::SETORAN, 2000000, 'Setoran tunai bulan kedua', $penabungs[0]);
            $ledgerService->record($plan1, LedgerType::SETORAN, 2000000, 'Setoran pelunasan persiapan kurban', $penabungs[0]);
        }

        // Plan 2: Penabung 2
        $plan2 = SavingsPlan::updateOrCreate(
            ['user_id' => $penabungs[1]->id, 'name' => 'Tabungan Kurban Sapi 2027'],
            [
                'tier_id' => $tierPremium->id,
                'target_amount' => 21000000,
                'target_year' => (int) date('Y') + 1,
                'monthly_amount' => 1750000,
                'status' => 'menabung',
                'autodebet_enabled' => false,
                'autodebet_day' => null,
                'balance_cached' => 0,
            ]
        );
        if ($plan2->ledgerEntries()->count() === 0) {
            $ledgerService->record($plan2, LedgerType::SETORAN, 1750000, 'Setoran cicilan ke-1', $penabungs[1]);
            $ledgerService->record($plan2, LedgerType::SETORAN, 1750000, 'Setoran cicilan ke-2', $penabungs[1]);
        }

        // Plan 3: Penabung 3
        $plan3 = SavingsPlan::updateOrCreate(
            ['user_id' => $penabungs[2]->id, 'name' => 'Tabungan Domba Priangan Garut'],
            [
                'tier_id' => $tierStandar->id,
                'target_amount' => 4000000,
                'target_year' => (int) date('Y'),
                'monthly_amount' => 1000000,
                'status' => 'menabung',
                'autodebet_enabled' => true,
                'autodebet_day' => 15,
                'balance_cached' => 0,
            ]
        );
        if ($plan3->ledgerEntries()->count() === 0) {
            $ledgerService->record($plan3, LedgerType::SETORAN, 2000000, 'Setoran awal rencana', $penabungs[2]);
            $ledgerService->record($plan3, LedgerType::SETORAN, 2000000, 'Setoran kedua autodebet', $penabungs[2]);
        }

        // Plan 4: Penabung 4
        $plan4 = SavingsPlan::updateOrCreate(
            ['user_id' => $penabungs[3]->id, 'name' => 'Tabungan Kurban Domba Berkah'],
            [
                'tier_id' => $tierEkonomis->id,
                'target_amount' => 3500000,
                'target_year' => (int) date('Y'),
                'monthly_amount' => 700000,
                'status' => 'menabung',
                'autodebet_enabled' => false,
                'autodebet_day' => null,
                'balance_cached' => 0,
            ]
        );
        if ($plan4->ledgerEntries()->count() === 0) {
            $ledgerService->record($plan4, LedgerType::SETORAN, 3500000, 'Setoran lunas mandiri', $penabungs[3]);
        }

        // Plan 5: Penabung 5
        $plan5 = SavingsPlan::updateOrCreate(
            ['user_id' => $penabungs[4]->id, 'name' => 'Tabungan Rutin Kambing Akikah/Kurban'],
            [
                'tier_id' => $tierStandar->id,
                'target_amount' => 4500000,
                'target_year' => (int) date('Y') + 1,
                'monthly_amount' => 500000,
                'status' => 'menabung',
                'autodebet_enabled' => true,
                'autodebet_day' => 25,
                'balance_cached' => 0,
            ]
        );
        if ($plan5->ledgerEntries()->count() === 0) {
            $ledgerService->record($plan5, LedgerType::SETORAN, 500000, 'Setoran autodebet bulan ke-1', $penabungs[4]);
            $ledgerService->record($plan5, LedgerType::SETORAN, 500000, 'Setoran autodebet bulan ke-2', $penabungs[4]);
            $ledgerService->record($plan5, LedgerType::SETORAN, 500000, 'Setoran autodebet bulan ke-3', $penabungs[4]);
        }

        // Demo Animals (Around 12 animals with mixed statuses: tayang, menunggu_review, dipesan, terjual) - PRD Section 12
        $demoAnimalsData = [
            [
                'title' => 'Domba Garut Super Tanduk Mewah DG-01',
                'type' => AnimalType::DOMBA,
                'age_months' => 16,
                'weight_kg' => 38.5,
                'health_notes' => 'Sehat sempurna, mata bening, tanduk kokoh melingkar simetris, bebas cacat dan penyakit.',
                'price' => 3750000,
                'region_id' => $regions['garut']->id,
                'status' => AnimalStatus::TERJUAL,
            ],
            [
                'title' => 'Domba Priangan Jantan Sehat DP-02',
                'type' => AnimalType::DOMBA,
                'age_months' => 14,
                'weight_kg' => 32.0,
                'health_notes' => 'Lincah, nafsu makan tinggi, bulu bersih dan gigi sudah poel satu pasang.',
                'price' => 3200000,
                'region_id' => $regions['garut']->id,
                'status' => AnimalStatus::DIPESAN,
                'reserved_by' => $penabungs[2]->id,
                'reserved_until' => now()->addHours(18),
            ],
            [
                'title' => 'Kambing Jawa Randu Gemuk KR-03',
                'type' => AnimalType::KAMBING,
                'age_months' => 15,
                'weight_kg' => 34.0,
                'health_notes' => 'Fisik tegap, postur panjang, sehat prima hasil penggemukan pakan fermentasi.',
                'price' => 3100000,
                'region_id' => $regions['bandung']->id,
                'status' => AnimalStatus::TAYANG,
            ],
            [
                'title' => 'Kambing Boer Campuran Jumbo KB-04',
                'type' => AnimalType::KAMBING,
                'age_months' => 18,
                'weight_kg' => 45.0,
                'health_notes' => 'Daging padat, postur lebar, sangat cocok untuk kurban berkualitas premium.',
                'price' => 4800000,
                'region_id' => $regions['bandung']->id,
                'status' => AnimalStatus::TAYANG,
            ],
            [
                'title' => 'Sapi Limosin Super Bobot 480kg SL-05',
                'type' => AnimalType::SAPI,
                'age_months' => 26,
                'weight_kg' => 480.0,
                'health_notes' => 'Telah poel, bebas cacing dan PMK, bersertifikat sehat dari dinas peternakan.',
                'price' => 24500000,
                'region_id' => $regions['garut']->id,
                'status' => AnimalStatus::TAYANG,
            ],
            [
                'title' => 'Sapi Bali Sehat Jantan SB-06',
                'type' => AnimalType::SAPI,
                'age_months' => 28,
                'weight_kg' => 320.0,
                'health_notes' => 'Karkas tinggi, lemak rendah, kulit bersih, aktif dan lincah.',
                'price' => 18000000,
                'region_id' => $regions['makassar']->id,
                'status' => AnimalStatus::TAYANG,
            ],
            [
                'title' => 'Domba Garut Kelas B DG-07',
                'type' => AnimalType::DOMBA,
                'age_months' => 13,
                'weight_kg' => 28.5,
                'health_notes' => 'Kondisi fisik bugar, siap sembelih.',
                'price' => 2700000,
                'region_id' => $regions['garut']->id,
                'status' => AnimalStatus::TERJUAL,
            ],
            [
                'title' => 'Kambing Etawa Jantan KE-08',
                'type' => AnimalType::KAMBING,
                'age_months' => 20,
                'weight_kg' => 52.0,
                'health_notes' => 'Telinga panjang simetris, postur tinggi besar.',
                'price' => 5500000,
                'region_id' => $regions['yogya']->id,
                'status' => AnimalStatus::TAYANG,
            ],
            [
                'title' => 'Domba Ekor Gemuk DEG-09',
                'type' => AnimalType::DOMBA,
                'age_months' => 14,
                'weight_kg' => 35.0,
                'health_notes' => 'Bulu tebal, sehat, poel pasang pertama.',
                'price' => 3400000,
                'region_id' => $regions['tasik']->id,
                'status' => AnimalStatus::MENUNGGU_REVIEW,
            ],
            [
                'title' => 'Sapi Simental Madura SM-10',
                'type' => AnimalType::SAPI,
                'age_months' => 25,
                'weight_kg' => 410.0,
                'health_notes' => 'Makan lahap, kondisi istimewa.',
                'price' => 22000000,
                'region_id' => $regions['tasik']->id,
                'status' => AnimalStatus::MENUNGGU_REVIEW,
            ],
            [
                'title' => 'Kambing Kacang Betina/Jantan KK-11',
                'type' => AnimalType::KAMBING,
                'age_months' => 15,
                'weight_kg' => 26.0,
                'health_notes' => 'Sehat dan lincah.',
                'price' => 2300000,
                'region_id' => $regions['makassar']->id,
                'status' => AnimalStatus::DIPESAN,
                'reserved_by' => $penabungs[0]->id,
                'reserved_until' => now()->addHours(12),
            ],
            [
                'title' => 'Domba Texel Lokal DT-12',
                'type' => AnimalType::DOMBA,
                'age_months' => 17,
                'weight_kg' => 42.0,
                'health_notes' => 'Postur bulat padat, pertumbuhan prima.',
                'price' => 4100000,
                'region_id' => $regions['bandung']->id,
                'status' => AnimalStatus::TAYANG,
            ],
        ];

        $animals = [];
        foreach ($demoAnimalsData as $data) {
            $animal = Animal::updateOrCreate(
                ['title' => $data['title']],
                [
                    'peternak_profile_id' => $profile1->id,
                    'type' => $data['type'],
                    'age_months' => $data['age_months'],
                    'weight_kg' => $data['weight_kg'],
                    'health_notes' => $data['health_notes'],
                    'price' => $data['price'],
                    'region_id' => $data['region_id'],
                    'status' => $data['status'],
                    'reserved_by' => $data['reserved_by'] ?? null,
                    'reserved_until' => $data['reserved_until'] ?? null,
                    'reviewed_by' => $admin->id,
                    'reviewed_at' => now()->subDay(),
                ]
            );

            if ($animal->media()->count() === 0) {
                $typeStr = $animal->type->value ?? $animal->type;
                if ($typeStr === 'sapi') {
                    $p1 = 'animals/sapi-01.jpg';
                    $p2 = 'animals/sapi-02.jpg';
                    $p3 = 'animals/sapi-03.jpg';
                } elseif ($typeStr === 'kambing') {
                    $p1 = 'animals/kambing-01.jpg';
                    $p2 = 'animals/kambing-02.jpg';
                    $p3 = 'animals/kambing-03.jpg';
                } else {
                    $p1 = 'animals/domba-01.jpg';
                    $p2 = 'animals/domba-02.jpg';
                    $p3 = 'animals/domba-03.jpg';
                }

                AnimalMedia::create(['animal_id' => $animal->id, 'type' => 'foto', 'path' => $p1, 'sort_order' => 1]);
                AnimalMedia::create(['animal_id' => $animal->id, 'type' => 'foto', 'path' => $p2, 'sort_order' => 2]);
                AnimalMedia::create(['animal_id' => $animal->id, 'type' => 'foto', 'path' => $p3, 'sort_order' => 3]);
                AnimalMedia::create(['animal_id' => $animal->id, 'type' => 'video', 'path' => 'animals/demo-video.mp4', 'sort_order' => 10]);
            }

            $animals[$data['title']] = $animal;
        }

        // Demo Orders at Various Statuses (PRD Section 12)
        // 1. Order Selesai (Sembelih Tim)
        $animal1 = $animals['Domba Garut Super Tanduk Mewah DG-01'];
        $order1 = Order::updateOrCreate(
            ['order_no' => 'ORD-2027-0001'],
            [
                'user_id' => $penabungs[0]->id,
                'savings_plan_id' => $plan1->id,
                'animal_id' => $animal1->id,
                'region_id' => $regions['garut']->id,
                'method' => OrderMethod::SEMBELIH_TIM,
                'status' => OrderStatus::SELESAI,
                'animal_price' => 3750000,
                'fee_slaughter' => 250000,
                'fee_distribution' => 150000,
                'fee_delivery' => 0,
                'commission' => 100000,
                'total_buyer_pays' => 4150000,
                'wakalah_agreed_at' => now()->subDays(6),
                'distribution_note' => 'Disalurkan kepada santri yatim dan kaum dhuafa Garut.',
                'peternak_ready_at' => now()->subDays(5),
                'scheduled_at' => now()->subDays(4),
                'slaughtered_at' => now()->subDays(3),
                'delivered_at' => now()->subDays(2),
                'confirmed_at' => now()->subDays(1),
                'is_disputed' => false,
            ]
        );

        if ($plan1->ledgerEntries()->where('type', LedgerType::PEMBELIAN)->count() === 0) {
            $ledgerService->record($plan1, LedgerType::PEMBELIAN, 4150000, 'Pembelian kurban ORD-2027-0001', $penabungs[0], Order::class, $order1->id, 'keluar');
        }

        Payout::updateOrCreate(
            ['order_id' => $order1->id],
            [
                'peternak_profile_id' => $profile1->id,
                'amount' => 3650000,
                'commission' => 100000,
                'status' => PayoutStatus::DIBAYARKAN,
                'released_at' => now()->subDays(1),
            ]
        );

        if ($order1->evidences()->count() === 0) {
            OrderEvidence::create(['order_id' => $order1->id, 'stage' => 'sembelih', 'type' => 'foto', 'path' => 'evidences/sembelih-01.jpg', 'uploaded_by' => $admin->id]);
            OrderEvidence::create(['order_id' => $order1->id, 'stage' => 'penyaluran', 'type' => 'foto', 'path' => 'evidences/penyaluran-01.jpg', 'uploaded_by' => $admin->id]);
        }

        if ($order1->statusLogs()->count() === 0) {
            OrderStatusLog::create(['order_id' => $order1->id, 'from_status' => 'direservasi', 'to_status' => 'dikonfirmasi', 'actor_id' => $penabungs[0]->id, 'created_at' => now()->subDays(6)]);
            OrderStatusLog::create(['order_id' => $order1->id, 'from_status' => 'dikonfirmasi', 'to_status' => 'dijadwalkan', 'actor_id' => $admin->id, 'created_at' => now()->subDays(4)]);
            OrderStatusLog::create(['order_id' => $order1->id, 'from_status' => 'dijadwalkan', 'to_status' => 'disembelih', 'actor_id' => $admin->id, 'created_at' => now()->subDays(3)]);
            OrderStatusLog::create(['order_id' => $order1->id, 'from_status' => 'disembelih', 'to_status' => 'disalurkan', 'actor_id' => $admin->id, 'created_at' => now()->subDays(2)]);
            OrderStatusLog::create(['order_id' => $order1->id, 'from_status' => 'disalurkan', 'to_status' => 'selesai', 'actor_id' => $penabungs[0]->id, 'created_at' => now()->subDays(1)]);
        }

        // 2. Order Disalurkan (Sembelih Tim - menunggu konfirmasi)
        $animal2 = $animals['Domba Garut Kelas B DG-07'];
        $order2 = Order::updateOrCreate(
            ['order_no' => 'ORD-2027-0002'],
            [
                'user_id' => $penabungs[3]->id,
                'savings_plan_id' => $plan4->id,
                'animal_id' => $animal2->id,
                'region_id' => $regions['garut']->id,
                'method' => OrderMethod::SEMBELIH_TIM,
                'status' => OrderStatus::DISALURKAN,
                'animal_price' => 2700000,
                'fee_slaughter' => 250000,
                'fee_distribution' => 150000,
                'fee_delivery' => 0,
                'commission' => 100000,
                'total_buyer_pays' => 3100000,
                'wakalah_agreed_at' => now()->subDays(3),
                'distribution_note' => 'Penyaluran via yayasan yatim.',
                'peternak_ready_at' => now()->subDays(2),
                'scheduled_at' => now()->subDays(2),
                'slaughtered_at' => now()->subDay(),
                'delivered_at' => now()->subHours(12),
                'auto_confirm_at' => now()->addHours(36),
                'is_disputed' => false,
            ]
        );

        if ($plan4->ledgerEntries()->where('type', LedgerType::PEMBELIAN)->count() === 0) {
            $ledgerService->record($plan4, LedgerType::PEMBELIAN, 3100000, 'Pembelian kurban ORD-2027-0002', $penabungs[3], Order::class, $order2->id, 'keluar');
        }

        Payout::updateOrCreate(
            ['order_id' => $order2->id],
            [
                'peternak_profile_id' => $profile1->id,
                'amount' => 2600000,
                'commission' => 100000,
                'status' => PayoutStatus::TERTAHAN,
            ]
        );

        if ($order2->evidences()->count() === 0) {
            OrderEvidence::create(['order_id' => $order2->id, 'stage' => 'sembelih', 'type' => 'foto', 'path' => 'evidences/sembelih-02.jpg', 'uploaded_by' => $admin->id]);
            OrderEvidence::create(['order_id' => $order2->id, 'stage' => 'penyaluran', 'type' => 'foto', 'path' => 'evidences/penyaluran-02.jpg', 'uploaded_by' => $admin->id]);
        }

        // 3. Order Dijadwalkan Kirim (Kirim Hidup)
        $animal3 = $animals['Domba Priangan Jantan Sehat DP-02'];
        $order3 = Order::updateOrCreate(
            ['order_no' => 'ORD-2027-0003'],
            [
                'user_id' => $penabungs[2]->id,
                'savings_plan_id' => $plan3->id,
                'animal_id' => $animal3->id,
                'region_id' => $regions['bandung']->id,
                'method' => OrderMethod::KIRIM_HIDUP,
                'status' => OrderStatus::DIJADWALKAN_KIRIM,
                'animal_price' => 3200000,
                'fee_slaughter' => 0,
                'fee_distribution' => 0,
                'fee_delivery' => 150000,
                'commission' => 100000,
                'total_buyer_pays' => 3350000,
                'recipient_name' => 'Bpk. H. Rahmat Hidayat',
                'recipient_phone' => '081234567890',
                'recipient_address' => 'Jl. Riau No. 88, Cibeunying, Bandung',
                'peternak_ready_at' => now()->subHours(6),
                'scheduled_at' => now()->addDay(),
                'is_disputed' => false,
            ]
        );

        if ($plan3->ledgerEntries()->where('type', LedgerType::PEMBELIAN)->count() === 0) {
            $ledgerService->record($plan3, LedgerType::PEMBELIAN, 3350000, 'Pembelian kurban ORD-2027-0003', $penabungs[2], Order::class, $order3->id, 'keluar');
        }

        Payout::updateOrCreate(
            ['order_id' => $order3->id],
            [
                'peternak_profile_id' => $profile1->id,
                'amount' => 3100000,
                'commission' => 100000,
                'status' => PayoutStatus::TERTAHAN,
            ]
        );

        if ($order3->notes()->count() === 0) {
            OrderNote::create([
                'order_id' => $order3->id,
                'user_id' => $admin->id,
                'body' => 'Armada pikap dijadwalkan menjemput hewan esok pagi pukul 07.30 WIB.',
            ]);
        }

        // Demo Tickets (3 types per PRD Section 7 & 12)
        // Ticket 1: Pertanyaan Pra-Beli (Penabung bertanya tentang hewan, reply dijadikan FAQ publik)
        $animalSL = $animals['Sapi Limosin Super Bobot 480kg SL-05'];
        $ticket1 = Ticket::updateOrCreate(
            ['subject' => 'Apakah sertifikat bebas PMK dan poel sapi SL-05 tersedia?'],
            [
                'type' => TicketType::PERTANYAAN_PRABELI,
                'creator_id' => $penabungs[1]->id,
                'animal_id' => $animalSL->id,
                'category' => 'Kesehatan Hewan',
                'status' => TicketStatus::SELESAI,
            ]
        );
        if ($ticket1->replies()->count() === 0) {
            TicketReply::create([
                'ticket_id' => $ticket1->id,
                'user_id' => $admin->id,
                'body' => 'Ya, seluruh sapi kurban YuKurban telah melalui uji kesehatan Dinas Peternakan dan memiliki SKKH resmi serta telah poel sempurna.',
                'is_faq_public' => true,
                'is_hidden' => false,
            ]);
        }

        // Ticket 2: Laporan Masalah (Terikat pesanan)
        $ticket2 = Ticket::updateOrCreate(
            ['subject' => 'Pertanyaan dokumentasi titik penyaluran daging kurban ORD-2027-0002'],
            [
                'type' => TicketType::LAPORAN_MASALAH,
                'creator_id' => $penabungs[3]->id,
                'order_id' => $order2->id,
                'category' => 'Dokumentasi',
                'status' => TicketStatus::DITANGGAPI,
            ]
        );
        if ($ticket2->replies()->count() === 0) {
            TicketReply::create([
                'ticket_id' => $ticket2->id,
                'user_id' => $admin->id,
                'body' => 'Wa\'alaikumussalam. Foto dokumentasi penimbangan dan pembagian daging telah berhasil diunggah ke rincian pesanan Anda.',
                'is_faq_public' => false,
                'is_hidden' => false,
            ]);
        }

        // Ticket 3: Bantuan Peternak
        $ticket3 = Ticket::updateOrCreate(
            ['subject' => 'Koordinasi jadwal kedatangan armada logistik Garut'],
            [
                'type' => TicketType::BANTUAN_PETERNAK,
                'creator_id' => $peternakUsers[0]->id,
                'category' => 'Operasional & Logistik',
                'status' => TicketStatus::DIBUKA,
            ]
        );

        // Demo Announcements (PRD Section 7 & 12)
        Announcement::updateOrCreate(
            ['title' => 'Pembukaan Resmi Jendela Pembelian Musim Kurban 1448 H / 2027 M'],
            [
                'body' => 'Alhamdulillah, jendela pemesanan kurban untuk metode sembelih tim dan kirim hidup resmi dibuka. Seluruh hewan di katalog telah melalui seleksi syariat dan pemeriksaan kesehatan ketat.',
                'audience' => 'semua',
                'created_by' => $admin->id,
                'published_at' => now()->subDays(3),
            ]
        );

        Announcement::updateOrCreate(
            ['title' => 'Panduan Akad Wakalah dan Fitur Autodebet YuKurban'],
            [
                'body' => 'Bagi shohibul qurban yang memilih metode sembelih tim, ikrar kuasa (wakalah) disetujui secara digital saat konfirmasi pesanan demi menjaga keabsahan syariat penyembelihan.',
                'audience' => 'penabung',
                'created_by' => $admin->id,
                'published_at' => now()->subDays(2),
            ]
        );

        Announcement::updateOrCreate(
            ['title' => 'Standar Sanitasi dan Prosedur Kesiapan Hewan di Kandang Mitra'],
            [
                'body' => 'Seluruh mitra peternak wajib memperbarui status kesiapan hewan di dashboard paling lambat H-1 jadwal keberangkatan armada logistik.',
                'audience' => 'peternak',
                'created_by' => $admin->id,
                'published_at' => now()->subDay(),
            ]
        );

        // Demo Contact Messages (Tamu)
        ContactMessage::updateOrCreate(
            ['email' => 'dkm.masjid.rayabandung@gmail.com'],
            [
                'name' => 'Pengurus DKM Al-Hikmah',
                'message' => 'Assalamu\'alaikum, kami ingin menanyakan paket kemitraan penyaluran daging kurban untuk 500 kepala keluarga di Jawa Barat.',
                'status' => 'baru',
            ]
        );

        ContactMessage::updateOrCreate(
            ['email' => 'hendra.kusuma@corp.id'],
            [
                'name' => 'Hendra Kusuma',
                'message' => 'Apakah platform YuKurban dapat menerbitkan invoice resmi dan bukti penyaluran untuk keperluan laporan pertanggungjawaban CSR perusahaan?',
                'status' => 'dibaca',
            ]
        );

        // Demo Audit Logs (PRD Section 7 & 8)
        if (AuditLog::count() === 0) {
            AuditLog::record($admin, 'verifikasi_peternak', $profile1, [
                'status' => 'terverifikasi',
                'farm_name' => $profile1->farm_name,
            ]);
            AuditLog::record($admin, 'review_hewan', $animal1, [
                'status' => 'tayang',
                'title' => $animal1->title,
                'price' => $animal1->price,
            ]);
            AuditLog::record($penabungs[0], 'buat_pesanan', $order1, [
                'order_no' => $order1->order_no,
                'total_buyer_pays' => $order1->total_buyer_pays,
            ]);
            AuditLog::record($admin, 'selesaikan_pesanan', $order1, [
                'order_no' => $order1->order_no,
                'status' => 'selesai',
            ]);
            AuditLog::record($admin, 'rilis_payout', $order1->payout, [
                'payout_id' => $order1->payout?->id,
                'amount' => $order1->payout?->amount,
            ]);
        }
    }
}
