<?php

namespace Tests\Feature;

use App\Enums\AnimalStatus;
use App\Enums\AnimalType;
use App\Enums\LedgerType;
use App\Enums\OrderMethod;
use App\Enums\OrderStatus;
use App\Enums\PayoutStatus;
use App\Enums\Role;
use App\Filament\Pages\ReportPage;
use App\Models\Animal;
use App\Models\AuditLog;
use App\Models\ContactMessage;
use App\Models\Order;
use App\Models\Payout;
use App\Models\PeternakProfile;
use App\Models\Region;
use App\Models\SavingsPlan;
use App\Models\User;
use App\Services\LedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

class Fase8FeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $penabung;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => Role::ADMIN,
            'is_active' => true,
        ]);

        $this->penabung = User::factory()->create([
            'role' => Role::PENABUNG,
            'is_active' => true,
        ]);
    }

    public function test_audit_log_record_helper_creates_entry(): void
    {
        $log = AuditLog::record($this->admin, 'verifikasi_peternak', $this->penabung, [
            'status' => 'terverifikasi',
            'catatan' => 'Dokumen lengkap',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'id' => $log->id,
            'actor_id' => $this->admin->id,
            'action' => 'verifikasi_peternak',
            'subject_type' => User::class,
            'subject_id' => $this->penabung->id,
        ]);

        $this->assertEquals('terverifikasi', $log->meta['status']);
        $this->assertEquals('Dokumen lengkap', $log->meta['catatan']);
    }

    public function test_admin_can_access_all_fase_8_filament_pages_and_resources(): void
    {
        $this->actingAs($this->admin)->get('/admin/payouts')->assertSuccessful();
        $this->actingAs($this->admin)->get('/admin/ledger-entries')->assertSuccessful();
        $this->actingAs($this->admin)->get('/admin/contact-messages')->assertSuccessful();
        $this->actingAs($this->admin)->get('/admin/audit-logs')->assertSuccessful();
        $this->actingAs($this->admin)->get('/admin/report-page')->assertSuccessful();
    }

    public function test_penabung_cannot_access_fase_8_admin_resources(): void
    {
        $this->actingAs($this->penabung)->get('/admin/payouts')->assertForbidden();
        $this->actingAs($this->penabung)->get('/admin/ledger-entries')->assertForbidden();
        $this->actingAs($this->penabung)->get('/admin/contact-messages')->assertForbidden();
        $this->actingAs($this->penabung)->get('/admin/audit-logs')->assertForbidden();
        $this->actingAs($this->penabung)->get('/admin/report-page')->assertForbidden();
    }

    public function test_report_page_csv_exports(): void
    {
        $region = Region::create(['province' => 'Jawa Barat', 'city' => 'Kota Bandung', 'is_active' => true]);
        $profile = PeternakProfile::create([
            'user_id' => $this->admin->id,
            'farm_name' => 'Kandang Barokah',
            'address' => 'Jl. Barokah No. 1',
            'region_id' => $region->id,
            'status' => 'terverifikasi',
        ]);

        $animal = Animal::create([
            'peternak_profile_id' => $profile->id,
            'title' => 'Domba Garut DG-99',
            'type' => AnimalType::DOMBA,
            'age_months' => 15,
            'weight_kg' => 35.0,
            'health_notes' => 'Sehat prima, bebas penyakit.',
            'price' => 3500000,
            'region_id' => $region->id,
            'status' => AnimalStatus::TAYANG,
        ]);

        $plan = SavingsPlan::create([
            'user_id' => $this->penabung->id,
            'name' => 'Tabungan Qurban',
            'target_amount' => 5000000,
            'target_year' => 2027,
            'monthly_amount' => 500000,
            'status' => 'menabung',
            'balance_cached' => 0,
        ]);

        $ledgerService = app(LedgerService::class);
        $ledgerService->record($plan, LedgerType::SETORAN, 4000000, 'Setoran Uji Coba', $this->penabung);

        $order = Order::create([
            'order_no' => 'ORD-TEST-0001',
            'user_id' => $this->penabung->id,
            'savings_plan_id' => $plan->id,
            'animal_id' => $animal->id,
            'region_id' => $region->id,
            'method' => OrderMethod::SEMBELIH_TIM,
            'status' => OrderStatus::SELESAI,
            'animal_price' => 3500000,
            'fee_slaughter' => 250000,
            'fee_distribution' => 150000,
            'fee_delivery' => 0,
            'commission' => 100000,
            'total_buyer_pays' => 3900000,
        ]);

        $payout = Payout::create([
            'order_id' => $order->id,
            'peternak_profile_id' => $profile->id,
            'amount' => 3400000,
            'commission' => 100000,
            'status' => PayoutStatus::DIBAYARKAN,
            'released_at' => now(),
        ]);

        $reportPage = new ReportPage();

        // 1. Orders CSV Export
        $responseOrders = $reportPage->exportOrdersCsv();
        $this->assertInstanceOf(StreamedResponse::class, $responseOrders);
        $this->assertEquals('text/csv', $responseOrders->headers->get('Content-Type'));
        $this->assertStringContainsString('laporan_pesanan_', $responseOrders->headers->get('Content-Disposition'));

        ob_start();
        $responseOrders->sendContent();
        $csvOrdersContent = ob_get_clean();

        $this->assertStringContainsString('No. Pesanan', $csvOrdersContent);
        $this->assertStringContainsString('ORD-TEST-0001', $csvOrdersContent);

        // 2. Ledger CSV Export
        $responseLedger = $reportPage->exportLedgerCsv();
        $this->assertInstanceOf(StreamedResponse::class, $responseLedger);
        ob_start();
        $responseLedger->sendContent();
        $csvLedgerContent = ob_get_clean();

        $this->assertStringContainsString('Jenis Transaksi', $csvLedgerContent);
        $this->assertStringContainsString('Setoran Uji Coba', $csvLedgerContent);

        // 3. Payouts CSV Export
        $responsePayouts = $reportPage->exportPayoutsCsv();
        $this->assertInstanceOf(StreamedResponse::class, $responsePayouts);
        ob_start();
        $responsePayouts->sendContent();
        $csvPayoutsContent = ob_get_clean();

        $this->assertStringContainsString('Nominal Payout Bersih', $csvPayoutsContent);
        $this->assertStringContainsString('3400000', $csvPayoutsContent);
    }

    public function test_scheduled_reminders_command_runs_successfully(): void
    {
        $this->artisan('reminders:send')
            ->expectsOutputToContain('Reminders processed')
            ->assertSuccessful();
    }

    public function test_contact_message_mark_as_read(): void
    {
        $message = ContactMessage::create([
            'name' => 'Tamu Pengunjung',
            'email' => 'tamu@example.com',
            'message' => 'Pertanyaan kemitraan kurban',
            'status' => 'baru',
        ]);

        $this->assertEquals('baru', $message->status);

        $message->update(['status' => 'dibaca']);
        $this->assertEquals('dibaca', $message->fresh()->status);
    }
}
