<?php

namespace Database\Seeders;

use App\Enums\AnimalStatus;
use App\Enums\AnimalType;
use App\Enums\LedgerType;
use App\Enums\Role;
use App\Models\Animal;
use App\Models\AnimalMedia;
use App\Models\FeeRule;
use App\Models\PeternakProfile;
use App\Models\PriceTier;
use App\Models\Region;
use App\Models\SavingsPlan;
use App\Models\ServiceZone;
use App\Models\Setting;
use App\Models\SlaughterQuota;
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

        // Savings Plans
        $ledgerService = app(LedgerService::class);

        $plan1 = SavingsPlan::updateOrCreate(
            ['user_id' => $penabungs[0]->id, 'name' => 'Tabungan Kurban Kambing Standar'],
            [
                'tier_id' => $tierStandar->id,
                'target_amount' => 3600000,
                'target_year' => (int) date('Y'),
                'monthly_amount' => 300000,
                'status' => 'menabung',
                'autodebet_enabled' => true,
                'autodebet_day' => 10,
                'balance_cached' => 0,
            ]
        );

        if ($plan1->ledgerEntries()->count() === 0) {
            $ledgerService->record($plan1, LedgerType::SETORAN, 300000, 'Setoran awal tabungan', $penabungs[0]);
            $ledgerService->record($plan1, LedgerType::SETORAN, 300000, 'Setoran bulan kedua', $penabungs[0]);
        }

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
            $ledgerService->record($plan2, LedgerType::SETORAN, 1750000, 'Setoran perdana via VA', $penabungs[1]);
        }

        // Demo Animals (Around 12 animals with mixed statuses: tayang, menunggu_review, dipesan) - PRD Section 12
        $demoAnimalsData = [
            [
                'type' => AnimalType::DOMBA,
                'title' => 'Domba Garut Super Tanduk Mewah DG-01',
                'age_months' => 16,
                'weight_kg' => 38.5,
                'health_notes' => 'Sehat sempurna, mata bening, tanduk kokoh melingkar simetris, bebas cacat dan penyakit.',
                'price' => 3750000,
                'region_id' => $regions['garut']->id,
                'status' => AnimalStatus::TAYANG,
            ],
            [
                'type' => AnimalType::DOMBA,
                'title' => 'Domba Priangan Jantan Sehat DP-02',
                'age_months' => 14,
                'weight_kg' => 32.0,
                'health_notes' => 'Lincah, nafsu makan tinggi, bulu bersih dan gigi sudah poel satu pasang.',
                'price' => 3200000,
                'region_id' => $regions['garut']->id,
                'status' => AnimalStatus::TAYANG,
            ],
            [
                'type' => AnimalType::KAMBING,
                'title' => 'Kambing Jawa Randu Gemuk KR-03',
                'age_months' => 15,
                'weight_kg' => 34.0,
                'health_notes' => 'Fisik tegap, postur panjang, sehat prima hasil penggemukan pakan fermentasi.',
                'price' => 3100000,
                'region_id' => $regions['bandung']->id,
                'status' => AnimalStatus::TAYANG,
            ],
            [
                'type' => AnimalType::KAMBING,
                'title' => 'Kambing Boer Campuran Jumbo KB-04',
                'age_months' => 18,
                'weight_kg' => 45.0,
                'health_notes' => 'Daging padat, postur lebar, sangat cocok untuk kurban berkualitas premium.',
                'price' => 4800000,
                'region_id' => $regions['bandung']->id,
                'status' => AnimalStatus::TAYANG,
            ],
            [
                'type' => AnimalType::SAPI,
                'title' => 'Sapi Limosin Super Bobot 480kg SL-05',
                'age_months' => 26,
                'weight_kg' => 480.0,
                'health_notes' => 'Telah poel, bebas cacing dan PMK, bersertifikat sehat dari dinas peternakan.',
                'price' => 24500000,
                'region_id' => $regions['garut']->id,
                'status' => AnimalStatus::TAYANG,
            ],
            [
                'type' => AnimalType::SAPI,
                'title' => 'Sapi Bali Sehat Jantan SB-06',
                'age_months' => 28,
                'weight_kg' => 320.0,
                'health_notes' => 'Karkas tinggi, lemak rendah, kulit bersih, aktif dan lincah.',
                'price' => 18000000,
                'region_id' => $regions['makassar']->id,
                'status' => AnimalStatus::TAYANG,
            ],
            [
                'type' => AnimalType::DOMBA,
                'title' => 'Domba Garut Kelas B DG-07',
                'age_months' => 13,
                'weight_kg' => 28.5,
                'health_notes' => 'Kondisi fisik bugar, siap sembelih.',
                'price' => 2700000,
                'region_id' => $regions['garut']->id,
                'status' => AnimalStatus::TAYANG,
            ],
            [
                'type' => AnimalType::KAMBING,
                'title' => 'Kambing Etawa Jantan KE-08',
                'age_months' => 20,
                'weight_kg' => 52.0,
                'health_notes' => 'Telinga panjang simetris, postur tinggi besar.',
                'price' => 5500000,
                'region_id' => $regions['yogya']->id,
                'status' => AnimalStatus::TAYANG,
            ],
            [
                'type' => AnimalType::DOMBA,
                'title' => 'Domba Ekor Gemuk DEG-09',
                'age_months' => 14,
                'weight_kg' => 35.0,
                'health_notes' => 'Bulu tebal, sehat, poel pasang pertama.',
                'price' => 3400000,
                'region_id' => $regions['tasik']->id,
                'status' => AnimalStatus::MENUNGGU_REVIEW,
            ],
            [
                'type' => AnimalType::SAPI,
                'title' => 'Sapi Simental Madura SM-10',
                'age_months' => 25,
                'weight_kg' => 410.0,
                'health_notes' => 'Makan lahap, kondisi istimewa.',
                'price' => 22000000,
                'region_id' => $regions['tasik']->id,
                'status' => AnimalStatus::MENUNGGU_REVIEW,
            ],
            [
                'type' => AnimalType::KAMBING,
                'title' => 'Kambing Kacang Betina/Jantan KK-11',
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
                'type' => AnimalType::DOMBA,
                'title' => 'Domba Texel Lokal DT-12',
                'age_months' => 17,
                'weight_kg' => 42.0,
                'health_notes' => 'Postur bulat padat, pertumbuhan prima.',
                'price' => 4100000,
                'region_id' => $regions['bandung']->id,
                'status' => AnimalStatus::TAYANG,
            ],
        ];

        foreach ($demoAnimalsData as $idx => $data) {
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

            // Add placeholder media if none exist
            if ($animal->media()->count() === 0) {
                // 3 photos
                AnimalMedia::create(['animal_id' => $animal->id, 'type' => 'foto', 'path' => 'animals/demo-front.jpg', 'sort_order' => 1]);
                AnimalMedia::create(['animal_id' => $animal->id, 'type' => 'foto', 'path' => 'animals/demo-side.jpg', 'sort_order' => 2]);
                AnimalMedia::create(['animal_id' => $animal->id, 'type' => 'foto', 'path' => 'animals/demo-full.jpg', 'sort_order' => 3]);
                // 1 video
                AnimalMedia::create(['animal_id' => $animal->id, 'type' => 'video', 'path' => 'animals/demo-video.mp4', 'sort_order' => 10]);
            }
        }
    }
}
