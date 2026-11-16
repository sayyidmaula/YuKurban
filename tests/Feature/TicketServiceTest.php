<?php

namespace Tests\Feature;

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
use App\Models\Announcement;
use App\Models\FeeRule;
use App\Models\Order;
use App\Models\PeternakProfile;
use App\Models\Region;
use App\Models\SavingsPlan;
use App\Models\ServiceZone;
use App\Models\Setting;
use App\Models\Ticket;
use App\Models\User;
use App\Services\LedgerService;
use App\Services\OrderService;
use App\Services\TicketService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TicketService $ticketService;
    protected OrderService $orderService;
    protected LedgerService $ledgerService;
    protected User $penabung;
    protected User $otherPenabung;
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

        $this->ticketService = app(TicketService::class);
        $this->orderService = app(OrderService::class);
        $this->ledgerService = app(LedgerService::class);

        Setting::set('reservation_hours', 24);
        Setting::set('report_deadline_hours', 48);
        Setting::set('purchase_window_sembelih_tim', [
            'open_at' => now()->subDays(2)->toIso8601String(),
            'close_at' => now()->addDays(30)->toIso8601String(),
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
            'delivery_fee' => 100000,
            'is_active' => true,
        ]);

        $this->feeRule = FeeRule::create([
            'animal_type' => AnimalType::KAMBING,
            'fee_slaughter' => 200000,
            'fee_distribution' => 100000,
            'commission_peternak' => 150000,
        ]);

        $this->penabung = User::create([
            'name' => 'Penabung Tiket',
            'email' => 'penabung.tiket@yukurban.test',
            'password' => bcrypt('password'),
            'role' => Role::PENABUNG,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->otherPenabung = User::create([
            'name' => 'Penabung Lain',
            'email' => 'penabung.lain@yukurban.test',
            'password' => bcrypt('password'),
            'role' => Role::PENABUNG,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->peternakUser = User::create([
            'name' => 'Peternak Tiket',
            'email' => 'peternak.tiket@yukurban.test',
            'password' => bcrypt('password'),
            'role' => Role::PETERNAK,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->adminUser = User::create([
            'name' => 'Admin Tiket',
            'email' => 'admin.tiket@yukurban.test',
            'password' => bcrypt('password'),
            'role' => Role::ADMIN,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->peternakProfile = PeternakProfile::create([
            'user_id' => $this->peternakUser->id,
            'farm_name' => 'Kandang Barokah Tiket',
            'address' => 'Jl. Peternakan No. 1',
            'region_id' => $this->region->id,
            'status' => 'terverifikasi',
        ]);

        $this->animal = Animal::create([
            'peternak_profile_id' => $this->peternakProfile->id,
            'region_id' => $this->region->id,
            'type' => AnimalType::KAMBING,
            'title' => 'Kambing Etawa Super',
            'age_months' => 24,
            'weight_kg' => 45.0,
            'price' => 4000000,
            'health_notes' => 'Sehat dan lincah',
            'status' => AnimalStatus::TAYANG,
        ]);

        $this->plan = SavingsPlan::create([
            'user_id' => $this->penabung->id,
            'name' => 'Tabungan Tiket',
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
            description: 'Saldo awal untuk pesanan',
            actor: $this->penabung
        );
        $this->plan->refresh();
    }

    public function test_create_pertanyaan_prabeli_ticket(): void
    {
        $ticket = $this->ticketService->createPrabeliTicket(
            user: $this->penabung,
            animal: $this->animal,
            subject: 'Apakah hewan ini sudah vaksin PMK lengkap?',
            body: 'Mohon info mengenai riwayat sertifikat vaksin hewan ini.'
        );

        $this->assertEquals(TicketType::PERTANYAAN_PRABELI, $ticket->type);
        $this->assertEquals(TicketStatus::DIBUKA, $ticket->status);
        $this->assertEquals($this->penabung->id, $ticket->creator_id);
        $this->assertEquals($this->animal->id, $ticket->animal_id);
        $this->assertNull($ticket->order_id);
        $this->assertCount(1, $ticket->replies);
    }

    public function test_create_bantuan_peternak_ticket(): void
    {
        $ticket = $this->ticketService->createBantuanPeternakTicket(
            user: $this->peternakUser,
            subject: 'Kendala upload foto kandang',
            body: 'Format gambar gagal diunggah dari browser ponsel.'
        );

        $this->assertEquals(TicketType::BANTUAN_PETERNAK, $ticket->type);
        $this->assertEquals(TicketStatus::DIBUKA, $ticket->status);
        $this->assertEquals($this->peternakUser->id, $ticket->creator_id);
        $this->assertCount(1, $ticket->replies);
    }

    public function test_create_laporan_masalah_ticket_enforces_ownership_and_dispute(): void
    {
        $order = $this->orderService->reserveOrder(
            $this->penabung,
            $this->plan,
            $this->animal,
            OrderMethod::SEMBELIH_TIM,
            $this->region
        );
        $this->orderService->confirmOrder($order, ['wakalah_agreed' => true]);

        // Attempting to report issue on another user's order must fail
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('hanya dapat membuat laporan masalah untuk pesanan milik Anda sendiri');

        $this->ticketService->createLaporanMasalahTicket(
            user: $this->otherPenabung,
            order: $order,
            subject: 'Laporan tidak sah',
            body: 'Bukan pesanan saya.'
        );
    }

    public function test_create_laporan_masalah_enforces_br_15_deadline(): void
    {
        $order = $this->orderService->reserveOrder(
            $this->penabung,
            $this->plan,
            $this->animal,
            OrderMethod::SEMBELIH_TIM,
            $this->region
        );
        $confirmed = $this->orderService->confirmOrder($order, ['wakalah_agreed' => true]);

        // Fast forward delivered_at to 50 hours ago (beyond 48 hours deadline)
        $confirmed->update([
            'delivered_at' => now()->subHours(50),
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Batas waktu pelaporan masalah');

        $this->ticketService->createLaporanMasalahTicket(
            user: $this->penabung,
            order: $confirmed,
            subject: 'Keluhan terlambat',
            body: 'Sudah lewat 50 jam.'
        );
    }

    public function test_reply_ticket_updates_status_and_supports_faq(): void
    {
        $ticket = $this->ticketService->createPrabeliTicket(
            $this->penabung,
            $this->animal,
            'Tanya Umur',
            'Berapa umur tepat kambing ini?'
        );

        $reply = $this->ticketService->replyTicket(
            ticket: $ticket,
            user: $this->adminUser,
            body: 'Kambing berusia 24 bulan dan sudah memenuhi kriteria musinnah.',
            isFaqPublic: true
        );

        $this->assertTrue($reply->is_faq_public);
        $ticket->refresh();
        $this->assertEquals(TicketStatus::DITANGGAPI, $ticket->status);
    }

    public function test_decision_dana_kembali_refunds_ledger_and_cancels_order_and_payout(): void
    {
        $order = $this->orderService->reserveOrder(
            $this->penabung,
            $this->plan,
            $this->animal,
            OrderMethod::SEMBELIH_TIM,
            $this->region
        );
        $confirmed = $this->orderService->confirmOrder($order, ['wakalah_agreed' => true]);

        $this->plan->refresh();
        $balanceBeforeRefund = $this->plan->balance_cached;

        $ticket = $this->ticketService->createLaporanMasalahTicket(
            user: $this->penabung,
            order: $confirmed,
            subject: 'Hewan tidak sesuai spesifikasi bobot',
            body: 'Bobot aktual jauh di bawah data tayang.'
        );

        // Order is marked as disputed
        $confirmed->refresh();
        $this->assertTrue($confirmed->is_disputed);

        // Admin decides DANA_KEMBALI
        $decidedTicket = $this->ticketService->decideLaporanMasalah(
            ticket: $ticket,
            decision: TicketDecision::DANA_KEMBALI,
            decisionNote: 'Laporan terverifikasi. Pengembalian dana penuh diproses.',
            admin: $this->adminUser
        );

        $this->assertEquals(TicketStatus::DIPUTUSKAN, $decidedTicket->status);
        $this->assertEquals(TicketDecision::DANA_KEMBALI, $decidedTicket->decision);

        // Ledger check (BR-01, BR-03)
        $this->plan->refresh();
        $expectedBalance = $balanceBeforeRefund + $confirmed->total_buyer_pays;
        $this->assertEquals($expectedBalance, $this->plan->balance_cached);

        $this->assertDatabaseHas('ledger_entries', [
            'savings_plan_id' => $this->plan->id,
            'type' => LedgerType::PENGEMBALIAN_DANA->value,
            'direction' => 'masuk',
            'amount' => $confirmed->total_buyer_pays,
            'reference_type' => Ticket::class,
            'reference_id' => $ticket->id,
        ]);

        // Order and payout cancelled
        $confirmed->refresh();
        $this->assertEquals(OrderStatus::DIBATALKAN, $confirmed->status);
        $this->assertFalse($confirmed->is_disputed);

        $this->assertEquals(PayoutStatus::DIBATALKAN, $confirmed->payout->status);
    }

    public function test_announcements_audience_scoping(): void
    {
        Announcement::create([
            'title' => 'Pengumuman Semua',
            'body' => 'Berita untuk semua pengguna',
            'audience' => 'semua',
            'created_by' => $this->adminUser->id,
            'published_at' => now()->subHour(),
        ]);

        Announcement::create([
            'title' => 'Pengumuman Penabung',
            'body' => 'Jadwal tabungan kurban',
            'audience' => 'penabung',
            'created_by' => $this->adminUser->id,
            'published_at' => now()->subHour(),
        ]);

        Announcement::create([
            'title' => 'Pengumuman Peternak',
            'body' => 'Standar kandang mitra',
            'audience' => 'peternak',
            'created_by' => $this->adminUser->id,
            'published_at' => now()->subHour(),
        ]);

        $penabungList = Announcement::forAudience('penabung')->get();
        $peternakList = Announcement::forAudience('peternak')->get();

        $this->assertCount(2, $penabungList);
        $this->assertTrue($penabungList->contains('title', 'Pengumuman Semua'));
        $this->assertTrue($penabungList->contains('title', 'Pengumuman Penabung'));
        $this->assertFalse($penabungList->contains('title', 'Pengumuman Peternak'));

        $this->assertCount(2, $peternakList);
        $this->assertTrue($peternakList->contains('title', 'Pengumuman Semua'));
        $this->assertTrue($peternakList->contains('title', 'Pengumuman Peternak'));
        $this->assertFalse($peternakList->contains('title', 'Pengumuman Penabung'));
    }
}
