<?php

use App\Models\User;

test('guest is redirected to login when visiting protected pages', function () {
    $this->get('/beranda')->assertRedirect(route('login'));
    $this->get('/akun')->assertRedirect(route('login'));
    $this->get('/peternak/beranda')->assertRedirect(route('login'));
});

test('penabung can access /beranda and /akun', function () {
    $penabung = User::factory()->penabung()->create();

    $this->actingAs($penabung)->get('/beranda')->assertStatus(200);
    $this->actingAs($penabung)->get('/akun')->assertStatus(200);
});

test('penabung is forbidden from accessing peternak routes', function () {
    $penabung = User::factory()->penabung()->create();

    $this->actingAs($penabung)->get('/peternak/beranda')->assertStatus(403);
    $this->actingAs($penabung)->get('/peternak/profil')->assertStatus(403);
});

test('peternak can access /peternak/beranda and /peternak/profil', function () {
    $peternak = User::factory()->peternak()->create();

    $this->actingAs($peternak)->get('/peternak/beranda')->assertStatus(200);
    $this->actingAs($peternak)->get('/peternak/profil')->assertStatus(200);
});

test('peternak is forbidden from accessing penabung routes', function () {
    $peternak = User::factory()->peternak()->create();

    $this->actingAs($peternak)->get('/beranda')->assertStatus(403);
    $this->actingAs($peternak)->get('/akun')->assertStatus(403);
});

test('inactive logged in user is logged out and blocked by middleware', function () {
    $user = User::factory()->penabung()->create(['is_active' => false]);

    $response = $this->actingAs($user)->get('/beranda');
    $response->assertRedirect(route('login'));
    $this->assertGuest();
});

