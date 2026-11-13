<?php

use App\Models\ContactMessage;

test('public pages are accessible to guests', function () {
    $this->get('/')->assertStatus(200);
    $this->get('/faq')->assertStatus(200);
    $this->get('/ketentuan')->assertStatus(200);
    $this->get('/hubungi')->assertStatus(200);
});

test('guest can submit contact form', function () {
    $response = $this->post('/hubungi', [
        'name' => 'Fulan',
        'email' => 'fulan@example.com',
        'message' => 'Pertanyaan mengenai pembagian daging kurban.',
    ]);

    $response->assertSessionHas('success');
    $this->assertDatabaseHas('contact_messages', [
        'name' => 'Fulan',
        'email' => 'fulan@example.com',
        'status' => 'baru',
    ]);
});

