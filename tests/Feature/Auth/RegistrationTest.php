<?php

use App\Enums\Role;
use App\Models\User;

test('registration screen can be rendered via /register and /daftar', function () {
    $this->get('/register')->assertStatus(200);
    $this->get('/daftar')->assertStatus(200);
});

test('new penabung can register', function () {
    $response = $this->post('/daftar', [
        'name' => 'Calon Penabung',
        'email' => 'penabung.baru@example.com',
        'phone' => '08123456789',
        'role' => 'penabung',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $user = User::where('email', 'penabung.baru@example.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->role)->toBe(Role::PENABUNG);
    $response->assertRedirect(route('beranda', absolute: false));
});

test('new peternak can register', function () {
    $response = $this->post('/daftar', [
        'name' => 'Peternak Berkah',
        'email' => 'peternak.baru@example.com',
        'phone' => '08987654321',
        'role' => 'peternak',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $user = User::where('email', 'peternak.baru@example.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->role)->toBe(Role::PETERNAK);
    $response->assertRedirect(route('peternak.beranda', absolute: false));
});

test('cannot register as admin', function () {
    $response = $this->post('/daftar', [
        'name' => 'Fake Admin',
        'email' => 'fake.admin@example.com',
        'role' => 'admin',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors('role');
    $this->assertGuest();
});
