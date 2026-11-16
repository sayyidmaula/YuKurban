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
use App\Models\SlaughterQuota;
use App\Models\User;
use App\Services\LedgerService;
use App\Services\OrderService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected OrderService $orderService;
    protected LedgerService $ledgerService;
    protected User $penabung;
    protected User $peternakUser;
    protected User $adminUser;
    protected PeternakProfile $peternakProfile;
    protected Region $region;
    protected ServiceZone $zone;
    protected FeeRule $feeRule;
    protected Animal $animal;
    protected SavingsPlan $plan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderService = app(OrderService::class);
        $this->ledgerService = app(LedgerService::class);

        Setting::set('reservation_hours', 24);
        Setting::set('auto_confirm_hours', 48);
        Setting::set('purchase_window_sembelih_tim', [
            'open_at' => now()->subDays(2)->toIso8601String(),
            'close_at' => now()->addDays(30)->toIso8601String(),
        ]);
        Setting::set('purchase_window_kirim_hidup', [
            'open_at' => now()->subDays(2)->toIso8601String(),
            'close_at' => now()->addDays(20)->toIso8601String(),
        ]);

        $this->region = Region::create([
            'city' => 'Kota Bandung',
            'province' => 'Jawa Barat',
            'is_active' => true,
        ]);

        $this->zone = ServiceZone::create([
            'region_id' => $this->region->id,
            'supports_sembelih_tim' => true,
            'supports_kirim_hidup' => true,
            'delivery_fee' => 150000,
            'is_active' => true,
        ]);

        $this->feeRule = FeeRule::create([
            'animal_type' => AnimalType::DOMBA,
            'fee_slaughter' => 200000,
            'fee_distribution' => 100000,
            'commission_peternak' => 150000,
        ]);

        $this->penabung = User::create([
            'name' => 'Penabung Uji Workflow',
            'email' => 'penabung.workflow@yukurban.test',
            'password' => bcrypt('password'),
            'role' => Role::PENABUNG,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->peternakUser = User::create([
            'name' => 'Peternak Mitra Uji',
            'email' => 'peternak.workflow@yukurban.test',
            'password' => bcrypt('password'),
            'role' => Role::PETERNAK,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->adminUser = User::create([
            'name' => 'Admin Workflow',
            'email' => 'admin.workflow@yukurban.test',
            'password' => bcrypt('password'),
            'role' => Role::ADMIN,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->peternakProfile = PeternakProfile::create([
            'user_id' => $this->peternakUser->id,
            'farm_name' => 'Kandang Barokah Alur',
            'address' => 'Jl. Cibaduyut No. 5',
            'region_id' => $this->region->id,
            'status' => 'terverifikasi',
        ]);

        $this->animal = Animal::create([
            'peternak_profile_id' => $this->peternakProfile->id,
            'region_id' => $this->region->id,
            'type' => AnimalType::DOMBA,
            'title' => 'Domba Garut Gagah',
            'age_months' => 24,
            'weight_kg' => 40.0,
            'price' => 3500000,
            'health_notes' => 'Sehat dan bersertifikat',
            'status' => AnimalStatus::TAYANG,
        ]);

        $this->plan = SavingsPlan::create([
            'user_id' => $this->penabung->id,
            'name' => 'Tabungan Qurban 2027',
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
            description: 'Saldo awal untuk pembelian kurban',
            actor: $this->penabung
        );
        $this->plan->refresh();
    }

    protected function createConfirmedOrder(OrderMethod $method = OrderMethod::SEMBELIH_TIM): Order
    {
        $order = $this->orderService->reserveOrder(
            $this->penabung,
            $this->plan,
            $this->animal,
            $method,
            $this->region
        );

        $confirmData = $method === OrderMethod::SEMBELIH_TIM
            ? ['wakalah_agreed' => true, 'distribution_note' => 'RT 01']
            : ['recipient_name' => 'Budi', 'recipient_phone' => '0812345678', 'recipient_address' => 'Jl. Merdeka'];

        return $this->orderService->confirmOrder($order, $confirmData);
    }

    public function test_sembelih_tim_full_workflow_to_selesai(): void
    {
        $order = $this->createConfirmedOrder(OrderMethod::SEMBELIH_TIM);

        // Set slaughter quota for tomorrow (BR-08)
        $scheduledDate = now()->addDays(2)->format('Y-m-d');
        SlaughterQuota::create([
            'date' => $scheduledDate,
            'quota' => 10,
        ]);

        // Step 1: Schedule sembelih tim
        $scheduled = $this->orderService->scheduleSembelihTim(
            $order,
            $scheduledDate,
            $this->adminUser,
            'Dijadwalkan di RPH Bandung'
        );

        $this->assertEquals(OrderStatus::DIJADWALKAN, $scheduled->status);
        $this->assertNotNull($scheduled->scheduled_at);

        // Step 2: Mark slaughtered
        $slaughtered = $this->orderService->markSlaughtered($scheduled, $this->adminUser);
        $this->assertEquals(OrderStatus::DISEMBELIH, $slaughtered->status);
        $this->assertNotNull($slaughtered->slaughtered_at);

        // Step 3: Mark distributed
        $distributed = $this->orderService->markDistributed($slaughtered, $this->adminUser);
        $this->assertEquals(OrderStatus::DISALURKAN, $distributed->status);
        $this->assertNotNull($distributed->delivered_at);
        $this->assertNotNull($distributed->auto_confirm_at);

        // Step 4: Complete order & release payout (BR-13)
        $completed = $this->orderService->completeOrder($distributed, $this->penabung);
        $this->assertEquals(OrderStatus::SELESAI, $completed->status);

        // Payout released
        $this->assertEquals(PayoutStatus::DIBAYARKAN, $completed->payout->status);
        $this->assertNotNull($completed->payout->released_at);

        // Animal status is now TERJUAL
        $this->animal->refresh();
        $this->assertEquals(AnimalStatus::TERJUAL, $this->animal->status);
    }

    public function test_sembelih_tim_scheduling_rejects_full_quota_br_08(): void
    {
        $order = $this->createConfirmedOrder(OrderMethod::SEMBELIH_TIM);

        $targetDate = now()->addDays(3)->format('Y-m-d');
        // Quota is 0
        SlaughterQuota::create([
            'date' => $targetDate,
            'quota' => 0,
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('telah penuh');

        $this->orderService->scheduleSembelihTim($order, $targetDate, $this->adminUser);
    }

    public function test_kirim_hidup_full_workflow_to_selesai(): void
    {
        $order = $this->createConfirmedOrder(OrderMethod::KIRIM_HIDUP);

        // Step 1: Schedule delivery
        $deliverDate = now()->addDays(2)->format('Y-m-d H:i:s');
        $scheduled = $this->orderService->scheduleKirimHidup($order, $deliverDate, $this->adminUser);
        $this->assertEquals(OrderStatus::DIJADWALKAN_KIRIM, $scheduled->status);

        // Step 2: Mark in transit
        $inTransit = $this->orderService->markInTransit($scheduled, $this->adminUser);
        $this->assertEquals(OrderStatus::DALAM_PERJALANAN, $inTransit->status);

        // Step 3: Mark delivered
        $delivered = $this->orderService->markDelivered($inTransit, $this->adminUser);
        $this->assertEquals(OrderStatus::DITERIMA, $delivered->status);
        $this->assertNotNull($delivered->delivered_at);
        $this->assertNotNull($delivered->auto_confirm_at);

        // Step 4: Complete order
        $completed = $this->orderService->completeOrder($delivered, $this->penabung);
        $this->assertEquals(OrderStatus::SELESAI, $completed->status);
        $this->assertEquals(PayoutStatus::DIBAYARKAN, $completed->payout->status);

        $this->animal->refresh();
        $this->assertEquals(AnimalStatus::TERJUAL, $this->animal->status);
    }

    public function test_invalid_transitions_are_rejected(): void
    {
        $order = $this->createConfirmedOrder(OrderMethod::SEMBELIH_TIM);

        // Cannot skip directly from DIKONFIRMASI to DISEMBELIH
        $this->expectException(Exception::class);
        $this->orderService->markSlaughtered($order, $this->adminUser);
    }

    public function test_peternak_mark_ready(): void
    {
        $order = $this->createConfirmedOrder(OrderMethod::SEMBELIH_TIM);

        $this->assertNull($order->peternak_ready_at);

        $readyOrder = $this->orderService->markPeternakReady($order, $this->peternakUser);

        $this->assertNotNull($readyOrder->peternak_ready_at);
    }

    public function test_add_evidence_and_note(): void
    {
        $order = $this->createConfirmedOrder(OrderMethod::SEMBELIH_TIM);

        $evidence = $this->orderService->addEvidence(
            $order,
            'sembelih',
            'foto',
            'evidences/sembelih_123.jpg',
            $this->adminUser
        );

        $this->assertDatabaseHas('order_evidences', [
            'order_id' => $order->id,
            'stage' => 'sembelih',
            'type' => 'foto',
            'path' => 'evidences/sembelih_123.jpg',
        ]);

        $note = $this->orderService->addNote(
            $order,
            $this->adminUser,
            'Koordinasi dengan peternak berjalan lancar'
        );

        $this->assertDatabaseHas('order_notes', [
            'order_id' => $order->id,
            'user_id' => $this->adminUser->id,
            'body' => 'Koordinasi dengan peternak berjalan lancar',
        ]);
    }

    public function test_auto_confirm_orders_scheduled_job(): void
    {
        $order = $this->createConfirmedOrder(OrderMethod::SEMBELIH_TIM);

        $scheduledDate = now()->addDay()->format('Y-m-d');
        SlaughterQuota::create(['date' => $scheduledDate, 'quota' => 5]);
        $this->orderService->scheduleSembelihTim($order, $scheduledDate, $this->adminUser);
        $this->orderService->markSlaughtered($order, $this->adminUser);
        $distributed = $this->orderService->markDistributed($order, $this->adminUser);

        // Set auto_confirm_at in past
        $distributed->update(['auto_confirm_at' => now()->subMinute()]);

        $count = $this->orderService->autoConfirmOrders();

        $this->assertEquals(1, $count);

        $distributed->refresh();
        $this->assertEquals(OrderStatus::SELESAI, $distributed->status);
        $this->assertEquals(PayoutStatus::DIBAYARKAN, $distributed->payout->status);
    }

    public function test_disputed_order_is_not_auto_confirmed(): void
    {
        $order = $this->createConfirmedOrder(OrderMethod::SEMBELIH_TIM);

        $scheduledDate = now()->addDay()->format('Y-m-d');
        SlaughterQuota::create(['date' => $scheduledDate, 'quota' => 5]);
        $this->orderService->scheduleSembelihTim($order, $scheduledDate, $this->adminUser);
        $this->orderService->markSlaughtered($order, $this->adminUser);
        $distributed = $this->orderService->markDistributed($order, $this->adminUser);

        // Mark as disputed (ada tiket laporan masalah)
        $distributed->update([
            'auto_confirm_at' => now()->subMinute(),
            'is_disputed' => true,
        ]);

        $count = $this->orderService->autoConfirmOrders();

        $this->assertEquals(0, $count);

        $distributed->refresh();
        $this->assertEquals(OrderStatus::DISALURKAN, $distributed->status);
    }
}
