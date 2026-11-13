<?php

use App\Models\User;

test('login screen can be rendered via /login and /masuk', function () {
    $this->get('/login')->assertStatus(200);
    $this->get('/masuk')->assertStatus(200);
});

test('penabung authenticates and redirects to /beranda', function () {
    $user = User::factory()->penabung()->create();

    $response = $this->post('/masuk', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('beranda', absolute: false));
});

test('peternak authenticates and redirects to /peternak/beranda', function () {
    $user = User::factory()->peternak()->create();

    $response = $this->post('/masuk', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('peternak.beranda', absolute: false));
});

test('inactive users cannot authenticate', function () {
    $user = User::factory()->create([
        'is_active' => false,
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertGuest();
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});
