<?php

use App\Models\PeternakProfile;
use App\Models\Region;
use App\Models\User;
use App\Services\AnimalService;

beforeEach(function () {
    $this->peternak = User::factory()->peternak()->create();
    $this->admin = User::factory()->admin()->create();
    $this->region = Region::create([
        'province' => 'Jawa Barat',
        'city' => 'Kabupaten Garut',
        'is_active' => true,
    ]);
});

test('peternak can submit profile and status is menunggu', function () {
    $response = $this->actingAs($this->peternak)->post('/peternak/profil', [
        'farm_name' => 'Kandang Barokah Al-Falah',
        'address' => 'Jl. Rancabango KM 5',
        'region_id' => $this->region->id,
        'description' => 'Kandang pembibitan dan penggemukan domba.',
    ]);

    $response->assertSessionHas('success');

    $profile = PeternakProfile::where('user_id', $this->peternak->id)->first();
    expect($profile)->not->toBeNull()
        ->and($profile->status)->toBe('menunggu')
        ->and($profile->farm_name)->toBe('Kandang Barokah Al-Falah');
});

test('admin can verify or reject peternak profile', function () {
    $profile = PeternakProfile::create([
        'user_id' => $this->peternak->id,
        'farm_name' => 'Kandang Uji Verifikasi',
        'address' => 'Jl. Uji No. 1',
        'region_id' => $this->region->id,
        'status' => 'menunggu',
    ]);

    $service = app(AnimalService::class);

    // Verify approve
    $service->verifyPeternak($profile, $this->admin, true);
    $profile->refresh();
    expect($profile->status)->toBe('terverifikasi')
        ->and($profile->verified_by)->toBe($this->admin->id)
        ->and($profile->isVerified())->toBeTrue();

    // Verify reject
    $service->verifyPeternak($profile, $this->admin, false, 'Kandang belum layak sanitasi');
    $profile->refresh();
    expect($profile->status)->toBe('ditolak')
        ->and($profile->rejection_reason)->toBe('Kandang belum layak sanitasi');
});

