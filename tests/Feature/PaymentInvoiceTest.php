<?php

use App\Models\PaymentInvoice;
use App\Models\SavingsPlan;
use App\Models\User;
use App\Services\InvoiceService;

beforeEach(function () {
    $this->invoiceService = app(InvoiceService::class);
    $this->user = User::factory()->penabung()->create();
    $this->plan = SavingsPlan::create([
        'user_id' => $this->user->id,
        'name' => 'Rencana Tabungan Invoice',
        'target_amount' => 3000000,
        'target_year' => (int) date('Y'),
        'monthly_amount' => 300000,
        'status' => 'menabung',
        'balance_cached' => 0,
    ]);
});

test('can create virtual account invoice with 16 digits and 24 hours expiry', function () {
    $invoice = $this->invoiceService->createInvoice(
        plan: $this->plan,
        amount: 300000,
        source: 'manual'
    );

    expect(strlen($invoice->va_code))->toBe(16)
        ->and($invoice->status)->toBe('menunggu')
        ->and($invoice->amount)->toBe(300000)
        ->and((int) round(now()->diffInHours($invoice->expires_at)))->toBe(24);
});

test('simulating successful payment adds ledger entry and increases balance', function () {
    $invoice = $this->invoiceService->createInvoice(
        plan: $this->plan,
        amount: 500000
    );

    $response = $this->actingAs($this->user)->post("/tagihan/{$invoice->id}/simulasi-berhasil");

    $response->assertRedirect(route('tagihan.show', $invoice->id));

    $invoice->refresh();
    $this->plan->refresh();

    expect($invoice->status)->toBe('berhasil')
        ->and($invoice->paid_at)->not->toBeNull()
        ->and($this->plan->balance_cached)->toBe(500000);

    $this->assertDatabaseHas('ledger_entries', [
        'savings_plan_id' => $this->plan->id,
        'type' => 'setoran',
        'amount' => 500000,
        'direction' => 'masuk',
    ]);
});

test('simulating failed payment marks invoice failed and does not change balance', function () {
    $invoice = $this->invoiceService->createInvoice(
        plan: $this->plan,
        amount: 500000
    );

    $response = $this->actingAs($this->user)->post("/tagihan/{$invoice->id}/simulasi-gagal");

    $response->assertRedirect(route('tagihan.show', $invoice->id));

    $invoice->refresh();
    $this->plan->refresh();

    expect($invoice->status)->toBe('gagal')
        ->and($this->plan->balance_cached)->toBe(0);

    $this->assertDatabaseMissing('ledger_entries', [
        'savings_plan_id' => $this->plan->id,
    ]);
});

test('expired invoice cannot be paid and command expires overdue invoices', function () {
    $invoice = PaymentInvoice::create([
        'user_id' => $this->user->id,
        'savings_plan_id' => $this->plan->id,
        'amount' => 300000,
        'va_code' => '8888123456789012',
        'status' => 'menunggu',
        'expires_at' => now()->subHour(),
        'source' => 'manual',
    ]);

    expect(fn () => $this->invoiceService->simulateSuccess($invoice))
        ->toThrow(RuntimeException::class, 'kedaluwarsa');

    // Run artisan command
    $this->artisan('invoices:expire')->assertSuccessful();

    $invoice->refresh();
    expect($invoice->status)->toBe('kedaluwarsa');
});
