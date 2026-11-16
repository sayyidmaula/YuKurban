<?php

namespace Tests\Feature;

use App\Enums\AnimalStatus;
use App\Enums\AnimalType;
use App\Enums\Role;
use App\Models\Animal;
use App\Models\FeeRule;
use App\Models\PeternakProfile;
use App\Models\Region;
use App\Models\ServiceZone;
use App\Models\Setting;
use App\Models\SlaughterQuota;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed basic settings
        Setting::set('purchase_window_sembelih_tim', [
            'open_at' => now()->subDay()->toIso8601String(),
            'close_at' => now()->addDays(30)->toIso8601String(),
        ]);
        Setting::set('purchase_window_kirim_hidup', [
            'open_at' => now()->addDays(10)->toIso8601String(),
            'close_at' => now()->addDays(25)->toIso8601String(),
        ]);
        Setting::set('idul_adha_date', '2027-06-16');
    }

    public function test_guest_is_redirected_from_admin_panel(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect('/admin/login');
    }

    public function test_penabung_cannot_access_admin_panel(): void
    {
        $penabung = User::factory()->create([
            'role' => Role::PENABUNG,
            'is_active' => true,
        ]);

        $response = $this->actingAs($penabung)->get('/admin');
        $response->assertForbidden();
    }

    public function test_peternak_cannot_access_admin_panel(): void
    {
        $peternak = User::factory()->create([
            'role' => Role::PETERNAK,
            'is_active' => true,
        ]);

        $response = $this->actingAs($peternak)->get('/admin');
        $response->assertForbidden();
    }

    public function test_inactive_admin_cannot_access_admin_panel(): void
    {
        $admin = User::factory()->create([
            'role' => Role::ADMIN,
            'is_active' => false,
        ]);

        $response = $this->actingAs($admin)->get('/admin');
        $response->assertForbidden();
    }

    public function test_active_admin_can_access_admin_dashboard_and_resources(): void
    {
        $admin = User::factory()->create([
            'role' => Role::ADMIN,
            'is_active' => true,
        ]);

        $this->actingAs($admin)->get('/admin')->assertSuccessful();
        $this->actingAs($admin)->get('/admin/service-zones')->assertSuccessful();
        $this->actingAs($admin)->get('/admin/fee-rules')->assertSuccessful();
        $this->actingAs($admin)->get('/admin/slaughter-quotas')->assertSuccessful();
        $this->actingAs($admin)->get('/admin/peternak-profiles')->assertSuccessful();
        $this->actingAs($admin)->get('/admin/animals')->assertSuccessful();
        $this->actingAs($admin)->get('/admin/settings')->assertSuccessful();
    }

    public function test_service_zone_support_and_delivery_fee_br07(): void
    {
        $bandung = Region::create(['province' => 'Jawa Barat', 'city' => 'Kota Bandung', 'is_active' => true]);
        $tasik = Region::create(['province' => 'Jawa Barat', 'city' => 'Kabupaten Tasikmalaya', 'is_active' => true]);

        $zoneBandung = ServiceZone::create([
            'region_id' => $bandung->id,
            'supports_sembelih_tim' => true,
            'supports_kirim_hidup' => true,
            'delivery_fee' => 150000,
            'is_active' => true,
        ]);

        $zoneTasik = ServiceZone::create([
            'region_id' => $tasik->id,
            'supports_sembelih_tim' => true,
            'supports_kirim_hidup' => false,
            'delivery_fee' => 0,
            'is_active' => true,
        ]);

        $this->assertTrue($zoneBandung->supports_sembelih_tim);
        $this->assertTrue($zoneBandung->supports_kirim_hidup);
        $this->assertEquals(150000, $zoneBandung->delivery_fee);

        $this->assertTrue($zoneTasik->supports_sembelih_tim);
        $this->assertFalse($zoneTasik->supports_kirim_hidup);
        $this->assertEquals(0, $zoneTasik->delivery_fee);
    }

    public function test_slaughter_quota_availability_and_capacity_br08(): void
    {
        $targetDate = '2027-06-16';

        $quota = SlaughterQuota::create([
            'date' => $targetDate,
            'quota' => 20,
        ]);

        $this->assertEquals(20, SlaughterQuota::getRemainingQuota($targetDate));

        // When date has no quota configured
        $this->assertEquals(0, SlaughterQuota::getRemainingQuota('2027-06-25'));
    }

    public function test_fee_rules_configuration_per_animal_type_br09(): void
    {
        FeeRule::create([
            'animal_type' => AnimalType::KAMBING,
            'fee_slaughter' => 250000,
            'fee_distribution' => 150000,
            'commission_peternak' => 100000,
        ]);

        FeeRule::create([
            'animal_type' => AnimalType::SAPI,
            'fee_slaughter' => 1500000,
            'fee_distribution' => 800000,
            'commission_peternak' => 500000,
        ]);

        $kambingRule = FeeRule::getForAnimalType(AnimalType::KAMBING);
        $this->assertNotNull($kambingRule);
        $this->assertEquals(250000, $kambingRule->fee_slaughter);
        $this->assertEquals(150000, $kambingRule->fee_distribution);
        $this->assertEquals(100000, $kambingRule->commission_peternak);

        $sapiRule = FeeRule::getForAnimalType('sapi');
        $this->assertNotNull($sapiRule);
        $this->assertEquals(1500000, $sapiRule->fee_slaughter);
        $this->assertEquals(800000, $sapiRule->fee_distribution);
        $this->assertEquals(500000, $sapiRule->commission_peternak);
    }

    public function test_purchase_windows_configuration_br05(): void
    {
        $windowSembelih = Setting::get('purchase_window_sembelih_tim');
        $windowKirim = Setting::get('purchase_window_kirim_hidup');

        $now = now();

        // Sembelih tim is currently open (subDay to +30Days)
        $isSembelihOpen = $now->between($windowSembelih['open_at'], $windowSembelih['close_at']);
        $this->assertTrue($isSembelihOpen);

        // Kirim hidup is not yet open (+10Days)
        $isKirimOpen = $now->between($windowKirim['open_at'], $windowKirim['close_at']);
        $this->assertFalse($isKirimOpen);
    }

    public function test_admin_can_verify_and_reject_peternak_profile(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $user = User::factory()->create(['role' => Role::PETERNAK]);
        $region = Region::create(['province' => 'Jawa Barat', 'city' => 'Garut', 'is_active' => true]);

        $profile = PeternakProfile::create([
            'user_id' => $user->id,
            'farm_name' => 'Kandang Barokah',
            'address' => 'Jl. Desa No. 1',
            'region_id' => $region->id,
            'status' => 'menunggu',
        ]);

        $this->assertEquals('menunggu', $profile->status);

        // Verification
        $profile->update([
            'status' => 'terverifikasi',
            'verified_by' => $admin->id,
            'verified_at' => now(),
        ]);

        $this->assertEquals('terverifikasi', $profile->fresh()->status);
        $this->assertEquals($admin->id, $profile->fresh()->verified_by);

        // Rejection
        $profile->update([
            'status' => 'ditolak',
            'rejection_reason' => 'Dokumen izin kandang tidak lengkap.',
        ]);

        $this->assertEquals('ditolak', $profile->fresh()->status);
        $this->assertEquals('Dokumen izin kandang tidak lengkap.', $profile->fresh()->rejection_reason);
    }

    public function test_admin_can_approve_animal_to_tayang(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $user = User::factory()->create(['role' => Role::PETERNAK]);
        $region = Region::create(['province' => 'Jawa Barat', 'city' => 'Garut', 'is_active' => true]);

        $profile = PeternakProfile::create([
            'user_id' => $user->id,
            'farm_name' => 'Kandang Barokah',
            'address' => 'Jl. Desa No. 1',
            'region_id' => $region->id,
            'status' => 'terverifikasi',
        ]);

        $animal = Animal::create([
            'peternak_profile_id' => $profile->id,
            'type' => AnimalType::KAMBING,
            'title' => 'Kambing Kacang Super',
            'age_months' => 14,
            'weight_kg' => 30.5,
            'health_notes' => 'Sehat prima',
            'price' => 3000000,
            'region_id' => $region->id,
            'status' => AnimalStatus::MENUNGGU_REVIEW,
        ]);

        $this->assertEquals(AnimalStatus::MENUNGGU_REVIEW, $animal->status);

        $animal->update([
            'status' => AnimalStatus::TAYANG,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
            'review_note' => 'Lolos verifikasi kesehatan dan umur.',
        ]);

        $this->assertEquals(AnimalStatus::TAYANG, $animal->fresh()->status);
        $this->assertEquals('Lolos verifikasi kesehatan dan umur.', $animal->fresh()->review_note);
    }
}

