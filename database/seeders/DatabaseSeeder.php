<?php

namespace Database\Seeders;

use App\Enums\LedgerType;
use App\Enums\Role;
use App\Models\PriceTier;
use App\Models\SavingsPlan;
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

        // 3 Peternak
        for ($i = 1; $i <= 3; $i++) {
            User::updateOrCreate(
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

        // Sample savings plans with ledger entries for penabung 1 & 2
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
            $ledgerService->record(
                plan: $plan1,
                type: LedgerType::SETORAN,
                amount: 300000,
                description: 'Setoran awal tabungan kurban',
                actor: $penabungs[0]
            );
            $ledgerService->record(
                plan: $plan1,
                type: LedgerType::SETORAN,
                amount: 300000,
                description: 'Setoran bulan kedua via VA Simulasi',
                actor: $penabungs[0]
            );
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
            $ledgerService->record(
                plan: $plan2,
                type: LedgerType::SETORAN,
                amount: 1750000,
                description: 'Setoran perdana via VA Simulasi',
                actor: $penabungs[1]
            );
        }
    }
}
