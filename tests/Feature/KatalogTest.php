<?php

use App\Enums\AnimalStatus;
use App\Enums\AnimalType;
use App\Models\Animal;
use App\Models\AnimalMedia;
use App\Models\PeternakProfile;
use App\Models\Region;
use App\Models\User;

beforeEach(function () {
    $this->peternakUser = User::factory()->peternak()->create([
        'name' => 'Bapak Rahasia',
        'email' => 'private@peternak.test',
        'phone' => '081299998888',
    ]);

    $this->region = Region::create([
        'province' => 'Jawa Barat',
        'city' => 'Kabupaten Garut',
        'is_active' => true,
    ]);

    $this->profile = PeternakProfile::create([
        'user_id' => $this->peternakUser->id,
        'farm_name' => 'Kandang Barokah Publik',
        'address' => 'Jl. Samarang No. 100',
        'region_id' => $this->region->id,
        'status' => 'terverifikasi',
    ]);

    // 1 Tayang
    $this->animalTayang = Animal::create([
        'peternak_profile_id' => $this->profile->id,
        'title' => 'Domba Garut Tayang',
        'type' => AnimalType::DOMBA,
        'age_months' => 14,
        'weight_kg' => 35,
        'price' => 3500000,
        'region_id' => $this->region->id,
        'health_notes' => 'Sehat prima',
        'status' => AnimalStatus::TAYANG,
    ]);
    AnimalMedia::create(['animal_id' => $this->animalTayang->id, 'type' => 'foto', 'path' => 'p.jpg']);

    // 1 Menunggu review
    $this->animalReview = Animal::create([
        'peternak_profile_id' => $this->profile->id,
        'title' => 'Domba Belum Tayang',
        'type' => AnimalType::DOMBA,
        'age_months' => 14,
        'weight_kg' => 32,
        'price' => 3200000,
        'region_id' => $this->region->id,
        'health_notes' => 'Masih kurasi',
        'status' => AnimalStatus::MENUNGGU_REVIEW,
    ]);
});

test('guest can access katalog and only view published animals', function () {
    $response = $this->get('/katalog');
    $response->assertStatus(200);

    $response->assertSee('Domba Garut Tayang');
    $response->assertDontSee('Domba Belum Tayang');
});

test('BR-10: katalog does not leak peternak personal phone, email, or user name', function () {
    $response = $this->get('/katalog');
    $response->assertStatus(200);

    // Assert that peternak personal information is not exposed
    $response->assertDontSee('081299998888');
    $response->assertDontSee('private@peternak.test');
    $response->assertDontSee('Bapak Rahasia');

    // But farm name is visible
    $response->assertSee('Kandang Barokah Publik');
});

test('animal detail page is viewable and respects privacy', function () {
    $response = $this->get("/katalog/{$this->animalTayang->id}");
    $response->assertStatus(200);

    $response->assertSee('Domba Garut Tayang');
    $response->assertDontSee('081299998888');
    $response->assertDontSee('private@peternak.test');
});

test('unpublished animal detail cannot be viewed by guest', function () {
    $this->get("/katalog/{$this->animalReview->id}")->assertStatus(404);
});

