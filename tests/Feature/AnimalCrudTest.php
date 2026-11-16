<?php

use App\Enums\AnimalStatus;
use App\Enums\AnimalType;
use App\Models\Animal;
use App\Models\AnimalMedia;
use App\Models\PeternakProfile;
use App\Models\Region;
use App\Models\Setting;
use App\Models\User;
use App\Services\AnimalService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');

    Setting::set('min_age_months', [
        'kambing' => 12,
        'domba' => 12,
        'sapi' => 24,
    ]);

    $this->peternakUser = User::factory()->peternak()->create();
    $this->admin = User::factory()->admin()->create();

    $this->region = Region::create([
        'province' => 'Jawa Barat',
        'city' => 'Kota Bandung',
        'is_active' => true,
    ]);

    $this->profile = PeternakProfile::create([
        'user_id' => $this->peternakUser->id,
        'farm_name' => 'Kandang Barokah Bandung',
        'address' => 'Jl. Cibiru Raya',
        'region_id' => $this->region->id,
        'status' => 'terverifikasi',
    ]);
});

test('BR-16: peternak can create animal with 3 photos and 1 video', function () {
    $photos = [
        UploadedFile::fake()->image('depan.jpg'),
        UploadedFile::fake()->image('samping.jpg'),
        UploadedFile::fake()->image('keseluruhan.jpg'),
    ];
    $video = UploadedFile::fake()->create('kondisi.mp4', 1024, 'video/mp4');

    $response = $this->actingAs($this->peternakUser)->post('/peternak/hewan', [
        'title' => 'Domba Garut Gagah DG-01',
        'type' => 'domba',
        'age_months' => 14,
        'weight_kg' => 35.5,
        'price' => 3500000,
        'region_id' => $this->region->id,
        'health_notes' => 'Sehat dan lincah, tidak cacat.',
        'photos' => $photos,
        'video' => $video,
    ]);

    $response->assertRedirect(route('peternak.hewan.index'));

    $animal = Animal::where('title', 'Domba Garut Gagah DG-01')->first();
    expect($animal)->not->toBeNull()
        ->and($animal->type)->toBe(AnimalType::DOMBA)
        ->and($animal->status)->toBe(AnimalStatus::MENUNGGU_REVIEW)
        ->and($animal->photos()->count())->toBe(3)
        ->and($animal->videos()->count())->toBe(1);
});

test('BR-16: validation rejects animal under official minimum age', function () {
    $photos = [
        UploadedFile::fake()->image('1.jpg'),
        UploadedFile::fake()->image('2.jpg'),
        UploadedFile::fake()->image('3.jpg'),
    ];
    $video = UploadedFile::fake()->create('v.mp4', 1024, 'video/mp4');

    // Sapi minimum is 24 months, attempt with 18 months
    $response = $this->actingAs($this->peternakUser)->post('/peternak/hewan', [
        'title' => 'Sapi Belum Cukup Umur',
        'type' => 'sapi',
        'age_months' => 18,
        'weight_kg' => 300,
        'price' => 15000000,
        'region_id' => $this->region->id,
        'health_notes' => 'Sehat tapi masih muda.',
        'photos' => $photos,
        'video' => $video,
    ]);

    $response->assertSessionHasErrors('age_months');
    expect(Animal::where('title', 'Sapi Belum Cukup Umur')->exists())->toBeFalse();
});

test('peternak cannot edit animal belonging to other peternak', function () {
    $otherUser = User::factory()->peternak()->create();
    $otherProfile = PeternakProfile::create([
        'user_id' => $otherUser->id,
        'farm_name' => 'Kandang Lain',
        'address' => 'Jl. Lain',
        'region_id' => $this->region->id,
        'status' => 'terverifikasi',
    ]);

    $animal = Animal::create([
        'peternak_profile_id' => $otherProfile->id,
        'title' => 'Hewan Milik Peternak Lain',
        'type' => AnimalType::KAMBING,
        'age_months' => 15,
        'weight_kg' => 30,
        'price' => 3000000,
        'region_id' => $this->region->id,
        'health_notes' => 'Sehat',
        'status' => AnimalStatus::DRAFT,
    ]);

    $this->actingAs($this->peternakUser)->get("/peternak/hewan/{$animal->id}/edit")->assertStatus(403);
    $this->actingAs($this->peternakUser)->put("/peternak/hewan/{$animal->id}", [
        'title' => 'Hacked Title',
        'type' => 'kambing',
        'age_months' => 15,
        'weight_kg' => 30,
        'price' => 3000000,
        'region_id' => $this->region->id,
        'health_notes' => 'Sehat',
    ])->assertStatus(403);
});

test('admin can review and publish animal fulfilling BR-16', function () {
    $animal = Animal::create([
        'peternak_profile_id' => $this->profile->id,
        'title' => 'Kambing Siap Kurasi',
        'type' => AnimalType::KAMBING,
        'age_months' => 16,
        'weight_kg' => 35,
        'price' => 3500000,
        'region_id' => $this->region->id,
        'health_notes' => 'Sehat prima',
        'status' => AnimalStatus::MENUNGGU_REVIEW,
    ]);

    AnimalMedia::create(['animal_id' => $animal->id, 'type' => 'foto', 'path' => 'p1.jpg']);
    AnimalMedia::create(['animal_id' => $animal->id, 'type' => 'foto', 'path' => 'p2.jpg']);
    AnimalMedia::create(['animal_id' => $animal->id, 'type' => 'foto', 'path' => 'p3.jpg']);
    AnimalMedia::create(['animal_id' => $animal->id, 'type' => 'video', 'path' => 'v1.mp4']);

    $service = app(AnimalService::class);
    $service->reviewAnimal($animal, $this->admin, true, 'Kualitas fisik lolos kurasi');

    $animal->refresh();
    expect($animal->status)->toBe(AnimalStatus::TAYANG)
        ->and($animal->reviewed_by)->toBe($this->admin->id);
});

