<?php

namespace Tests\Feature;

use App\Enums\AnimalStatus;
use App\Enums\AnimalType;
use App\Enums\LedgerType;
use App\Enums\OrderMethod;
use App\Enums\OrderStatus;
use App\Enums\PayoutStatus;
use App\Enums\Role;
use App\Models\Animal;
use App\Models\FeeRule;
use App\Models\Order;
use App\Models\PeternakProfile;
use App\Models\Region;
use App\Models\SavingsPlan;
use App\Models\ServiceZone;
use App\Models\Setting;
use App\Models\User;
use App\Services\LedgerService;
use App\Services\OrderService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    protected OrderService $orderService;
    protected LedgerService $ledgerService;
    protected User $penabung;
    protected User $peternakUser;
    protected PeternakProfile $peternakProfile;
    protected Region $regionBandung;
    protected Region $regionGarut;
    protected ServiceZone $zoneBandung;
    protected ServiceZone $zoneGarut;
    protected FeeRule $feeRuleDomba;
    protected Animal $animal;
    protected SavingsPlan $plan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderService = app(OrderService::class);
        $this->ledgerService = app(LedgerService::class);

        // Settings (BR-05, BR-16)
        Setting::set('reservation_hours', 24);
        Setting::set('purchase_window_sembelih_tim', [
            'open_at' => now()->subDays(2)->toIso8601String(),
            'close_at' => now()->addDays(30)->toIso8601String(),
        ]);
        Setting::set('purchase_window_kirim_hidup', [
            'open_at' => now()->subDays(2)->toIso8601String(),
            'close_at' => now()->addDays(20)->toIso8601String(),
        ]);

        // Regions & Service Zones (BR-07)
        $this->regionBandung = Region::create([
            'city' => 'Kota Bandung',
            'province' => 'Jawa Barat',
            'is_active' => true,
        ]);
        $this->zoneBandung = ServiceZone::create([
            'region_id' => $this->regionBandung->id,
            'supports_sembelih_tim' => true,
            'supports_kirim_hidup' => true,
            'delivery_fee' => 150000,
            'is_active' => true,
        ]);

        $this->regionGarut = Region::create([
            'city' => 'Kabupaten Garut',
            'province' => 'Jawa Barat',
            'is_active' => true,
        ]);
        $this->zoneGarut = ServiceZone::create([
            'region_id' => $this->regionGarut->id,
            'supports_sembelih_tim' => true,
            'supports_kirim_hidup' => false, // Does NOT support kirim hidup
            'delivery_fee' => 0,
            'is_active' => true,
        ]);

        // Fee Rules (BR-09)
        $this->feeRuleDomba = FeeRule::create([
            'animal_type' => AnimalType::DOMBA,
            'fee_slaughter' => 200000,
            'fee_distribution' => 100000,
            'commission_peternak' => 150000,
        ]);

        // Users
        $this->penabung = User::create([
            'name' => 'Penabung Test',
            'email' => 'penabung@yukurban.test',
            'password' => bcrypt('password'),
            'role' => Role::PENABUNG,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->peternakUser = User::create([
            'name' => 'Peternak Test',
            'email' => 'peternak@yukurban.test',
            'password' => bcrypt('password'),
            'role' => Role::PETERNAK,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->peternakProfile = PeternakProfile::create([
            'user_id' => $this->peternakUser->id,
            'farm_name' => 'Kandang Barokah',
            'address' => 'Jl. Peternakan No. 10',
            'region_id' => $this->regionBandung->id,
            'status' => 'terverifikasi',
        ]);

        // Animal (TAYANG)
        $this->animal = Animal::create([
            'peternak_profile_id' => $this->peternakProfile->id,
            'region_id' => $this->regionBandung->id,
            'type' => AnimalType::DOMBA,
            'title' => 'Domba Garut Super A',
            'age_months' => 24,
            'weight_kg' => 35.5,
            'price' => 3000000,
            'health_notes' => 'Sehat terawat',
            'status' => AnimalStatus::TAYANG,
        ]);

        // Savings Plan with balance 5.000.000
        $this->plan = SavingsPlan::create([
            'user_id' => $this->penabung->id,
            'name' => 'Rencana Qurban 2027',
            'target_amount' => 5000000,
            'target_year' => 2027,
            'monthly_amount' => 500000,
            'status' => 'menabung',
            'balance_cached' => 0,
        ]);

        $this->ledgerService->record(
            plan: $this->plan,
            type: LedgerType::SETORAN,
            amount: 5000000,
            description: 'Setoran modal awal uji',
            actor: $this->penabung
        );
        $this->plan->refresh();
    }

    public function test_purchase_window_validation_br_05(): void
    {
        // Window is currently open
        $this->assertTrue($this->orderService->isPurchaseWindowOpen(OrderMethod::SEMBELIH_TIM));
        $this->assertTrue($this->orderService->isPurchaseWindowOpen(OrderMethod::KIRIM_HIDUP));

        // Close kirim hidup window
        Setting::set('purchase_window_kirim_hidup', [
            'open_at' => now()->addDays(5)->toIso8601String(),
            'close_at' => now()->addDays(15)->toIso8601String(),
        ]);

        $this->assertFalse($this->orderService->isPurchaseWindowOpen(OrderMethod::KIRIM_HIDUP));

        // Attempting to reserve kirim hidup when window is closed should throw
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Jendela pembelian untuk metode ini sedang ditutup.');

        $this->orderService->reserveOrder(
            $this->penabung,
            $this->plan,
            $this->animal,
            OrderMethod::KIRIM_HIDUP,
            $this->regionBandung
        );
    }

    public function test_service_zone_support_validation_br_07(): void
    {
        // Garut does not support kirim_hidup
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Metode Kirim Hewan Hidup tidak didukung untuk wilayah Kabupaten Garut.');

        $this->orderService->calculateCosts($this->animal, OrderMethod::KIRIM_HIDUP, $this->regionGarut);
    }

    public function test_cost_calculation_br_09(): void
    {
        // Sembelih Tim: animal_price (3.000.000) + slaughter (200.000) + distribution (100.000) = 3.300.000
        // Commission peternak: 150.000 -> payout peternak = 3.000.000 - 150.000 = 2.850.000
        $costsSembelih = $this->orderService->calculateCosts($this->animal, OrderMethod::SEMBELIH_TIM, $this->regionBandung);

        $this->assertEquals(3000000, $costsSembelih['animal_price']);
        $this->assertEquals(200000, $costsSembelih['fee_slaughter']);
        $this->assertEquals(100000, $costsSembelih['fee_distribution']);
        $this->assertEquals(0, $costsSembelih['fee_delivery']);
        $this->assertEquals(150000, $costsSembelih['commission']);
        $this->assertEquals(3300000, $costsSembelih['total_buyer_pays']);
        $this->assertEquals(2850000, $costsSembelih['payout_peternak']);

        // Kirim Hidup: animal_price (3.000.000) + delivery (150.000) = 3.150.000
        $costsKirim = $this->orderService->calculateCosts($this->animal, OrderMethod::KIRIM_HIDUP, $this->regionBandung);

        $this->assertEquals(3000000, $costsKirim['animal_price']);
        $this->assertEquals(0, $costsKirim['fee_slaughter']);
        $this->assertEquals(0, $costsKirim['fee_distribution']);
        $this->assertEquals(150000, $costsKirim['fee_delivery']);
        $this->assertEquals(150000, $costsKirim['commission']);
        $this->assertEquals(3150000, $costsKirim['total_buyer_pays']);
        $this->assertEquals(2850000, $costsKirim['payout_peternak']);
    }

    public function test_reserve_order_locks_animal_and_creates_reservation_br_06(): void
    {
        $order = $this->orderService->reserveOrder(
            $this->penabung,
            $this->plan,
            $this->animal,
            OrderMethod::SEMBELIH_TIM,
            $this->regionBandung
        );

        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals(OrderStatus::DIRESERVASI, $order->status);
        $this->assertEquals(3300000, $order->total_buyer_pays);
        $this->assertNotNull($order->reserved_until);

        // Animal status must be DIPESAN
        $this->animal->refresh();
        $this->assertEquals(AnimalStatus::DIPESAN, $this->animal->status);
        $this->assertEquals($this->penabung->id, $this->animal->reserved_by);

        // Another buyer cannot reserve the same animal
        $otherUser = User::create([
            'name' => 'Pembeli Lain',
            'email' => 'lain@yukurban.test',
            'password' => bcrypt('password'),
            'role' => Role::PENABUNG,
        ]);
        $otherPlan = SavingsPlan::create([
            'user_id' => $otherUser->id,
            'name' => 'Tabungan Lain',
            'target_amount' => 5000000,
            'target_year' => 2027,
            'monthly_amount' => 500000,
            'status' => 'menabung',
            'balance_cached' => 5000000,
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('sudah tidak berstatus tayang');

        $this->orderService->reserveOrder(
            $otherUser,
            $otherPlan,
            $this->animal,
            OrderMethod::SEMBELIH_TIM,
            $this->regionBandung
        );
    }

    public function test_reserve_order_fails_when_balance_insufficient(): void
    {
        $poorPlan = SavingsPlan::create([
            'user_id' => $this->penabung->id,
            'name' => 'Tabungan Kurang',
            'target_amount' => 5000000,
            'target_year' => 2027,
            'monthly_amount' => 500000,
            'status' => 'menabung',
            'balance_cached' => 1000000, // Only 1M, need 3.3M
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Saldo tabungan Anda tidak mencukupi');

        $this->orderService->reserveOrder(
            $this->penabung,
            $poorPlan,
            $this->animal,
            OrderMethod::SEMBELIH_TIM,
            $this->regionBandung
        );
    }

    public function test_confirm_order_sembelih_tim_requires_wakalah_br_12(): void
    {
        $order = $this->orderService->reserveOrder(
            $this->penabung,
            $this->plan,
            $this->animal,
            OrderMethod::SEMBELIH_TIM,
            $this->regionBandung
        );

        // Confirming without wakalah_agreed must fail
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Anda wajib menyetujui akad wakalah');

        $this->orderService->confirmOrder($order, [
            'wakalah_agreed' => false,
        ]);
    }

    public function test_confirm_order_kirim_hidup_requires_recipient_details_br_12(): void
    {
        $order = $this->orderService->reserveOrder(
            $this->penabung,
            $this->plan,
            $this->animal,
            OrderMethod::KIRIM_HIDUP,
            $this->regionBandung
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Nama, telepon, dan alamat penerima wajib diisi');

        $this->orderService->confirmOrder($order, [
            'recipient_name' => 'Ahmad',
            // Missing phone & address
        ]);
    }

    public function test_confirm_order_deducts_ledger_and_creates_held_payout_br_01_br_03_br_13(): void
    {
        $order = $this->orderService->reserveOrder(
            $this->penabung,
            $this->plan,
            $this->animal,
            OrderMethod::SEMBELIH_TIM,
            $this->regionBandung
        );

        $initialBalance = $this->plan->balance_cached; // 5.000.000

        $confirmed = $this->orderService->confirmOrder($order, [
            'wakalah_agreed' => true,
            'distribution_note' => 'Disalurkan ke warga RT 03 / RW 05',
        ]);

        $this->assertEquals(OrderStatus::DIKONFIRMASI, $confirmed->status);
        $this->assertNotNull($confirmed->confirmed_at);
        $this->assertNotNull($confirmed->wakalah_agreed_at);

        // Check Ledger Entry (BR-01, BR-03)
        $this->plan->refresh();
        $expectedBalance = $initialBalance - $order->total_buyer_pays; // 5.000.000 - 3.300.000 = 1.700.000
        $this->assertEquals($expectedBalance, $this->plan->balance_cached);

        $this->assertDatabaseHas('ledger_entries', [
            'savings_plan_id' => $this->plan->id,
            'type' => LedgerType::PEMBELIAN->value,
            'direction' => 'keluar',
            'amount' => 3300000,
            'balance_after' => 1700000,
            'reference_type' => Order::class,
            'reference_id' => $confirmed->id,
        ]);

        // Check Payout Escrow (BR-13)
        $this->assertDatabaseHas('payouts', [
            'order_id' => $confirmed->id,
            'peternak_profile_id' => $this->peternakProfile->id,
            'amount' => 2850000, // 3.000.000 - 150.000 commission
            'commission' => 150000,
            'status' => PayoutStatus::TERTAHAN->value,
        ]);
    }

    public function test_cancel_before_schedule_refunds_full_amount_and_cancels_payout_br_14(): void
    {
        $order = $this->orderService->reserveOrder(
            $this->penabung,
            $this->plan,
            $this->animal,
            OrderMethod::SEMBELIH_TIM,
            $this->regionBandung
        );

        $confirmed = $this->orderService->confirmOrder($order, [
            'wakalah_agreed' => true,
        ]);

        $this->plan->refresh();
        $this->assertEquals(1700000, $this->plan->balance_cached);

        // Cancel order before schedule
        $cancelled = $this->orderService->cancelBeforeSchedule($confirmed, $this->penabung, 'Perubahan rencana keluarga');

        $this->assertEquals(OrderStatus::DIBATALKAN, $cancelled->status);

        // Check refund ledger entry (BR-14)
        $this->plan->refresh();
        $this->assertEquals(5000000, $this->plan->balance_cached);

        $this->assertDatabaseHas('ledger_entries', [
            'savings_plan_id' => $this->plan->id,
            'type' => LedgerType::PENGEMBALIAN_DANA->value,
            'direction' => 'masuk',
            'amount' => 3300000,
            'balance_after' => 5000000,
            'reference_type' => Order::class,
            'reference_id' => $cancelled->id,
        ]);

        // Payout should be cancelled
        $this->assertDatabaseHas('payouts', [
            'order_id' => $cancelled->id,
            'status' => PayoutStatus::DIBATALKAN->value,
        ]);

        // Animal released back to TAYANG
        $this->animal->refresh();
        $this->assertEquals(AnimalStatus::TAYANG, $this->animal->status);
        $this->assertNull($this->animal->reserved_by);
    }

    public function test_expired_reservation_releases_animal_without_deduction(): void
    {
        $order = $this->orderService->reserveOrder(
            $this->penabung,
            $this->plan,
            $this->animal,
            OrderMethod::SEMBELIH_TIM,
            $this->regionBandung
        );

        // Fast-forward or trigger expireReservation
        $this->orderService->expireReservation($order);

        $order->refresh();
        $this->assertEquals(OrderStatus::KEDALUWARSA, $order->status);

        // Animal returned to TAYANG
        $this->animal->refresh();
        $this->assertEquals(AnimalStatus::TAYANG, $this->animal->status);
        $this->assertNull($this->animal->reserved_by);

        // Balance untouched
        $this->plan->refresh();
        $this->assertEquals(5000000, $this->plan->balance_cached);
    }

    public function test_peternak_order_privacy_omits_buyer_identity_br_10(): void
    {
        $order = $this->orderService->reserveOrder(
            $this->penabung,
            $this->plan,
            $this->animal,
            OrderMethod::KIRIM_HIDUP,
            $this->regionBandung
        );

        $this->orderService->confirmOrder($order, [
            'recipient_name' => 'Bapak Rahasia',
            'recipient_phone' => '081299998888',
            'recipient_address' => 'Jl. Rahasia Nomor 99 Bandung',
        ]);

        // Acting as peternak user
        $response = $this->actingAs($this->peternakUser)
            ->get(route('peternak.pesanan.index'));

        $response->assertOk();

        // In Inertia page props, order data must NOT contain recipient_name, recipient_phone, recipient_address, or buyer name
        $response->assertInertia(fn ($page) =>
            $page->component('Peternak/Order/Index')
                ->has('orders.data', 1)
                ->where('orders.data.0.order_no', $order->order_no)
                ->missing('orders.data.0.recipient_name')
                ->missing('orders.data.0.recipient_phone')
                ->missing('orders.data.0.recipient_address')
                ->missing('orders.data.0.user')
        );
    }
}
