<?php

use App\Enums\LedgerType;
use App\Models\LedgerEntry;
use App\Models\SavingsPlan;
use App\Models\User;
use App\Services\LedgerService;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->ledgerService = app(LedgerService::class);
    $this->user = User::factory()->penabung()->create();
    $this->plan = SavingsPlan::create([
        'user_id' => $this->user->id,
        'name' => 'Tabungan Uji Ledger',
        'target_amount' => 5000000,
        'target_year' => (int) date('Y'),
        'monthly_amount' => 500000,
        'status' => 'menabung',
        'balance_cached' => 0,
    ]);
});

test('BR-01: setoran menambah satu baris ledger dan saldo balance_cached sama dengan jumlah ledger', function () {
    $entry1 = $this->ledgerService->record(
        plan: $this->plan,
        type: LedgerType::SETORAN,
        amount: 500000,
        description: 'Setoran 1',
        actor: $this->user
    );

    expect($entry1->amount)->toBe(500000)
        ->and($entry1->balance_after)->toBe(500000)
        ->and($entry1->direction)->toBe('masuk');

    $this->plan->refresh();
    expect($this->plan->balance_cached)->toBe(500000);

    $entry2 = $this->ledgerService->record(
        plan: $this->plan,
        type: LedgerType::SETORAN,
        amount: 250000,
        description: 'Setoran 2',
        actor: $this->user
    );

    expect($entry2->amount)->toBe(250000)
        ->and($entry2->balance_after)->toBe(750000);

    $this->plan->refresh();
    expect($this->plan->balance_cached)->toBe(750000);

    // Sum of ledger must match cached balance
    $actualBalance = $this->ledgerService->calculateActualBalance($this->plan);
    expect($actualBalance)->toBe(750000);
});

test('BR-01: penarikan dana mengurangi saldo secara akurat', function () {
    $this->ledgerService->record(
        plan: $this->plan,
        type: LedgerType::SETORAN,
        amount: 1000000,
        description: 'Setoran awal',
        actor: $this->user
    );

    $withdrawEntry = $this->ledgerService->record(
        plan: $this->plan,
        type: LedgerType::PENARIKAN,
        amount: 400000,
        description: 'Penarikan dana kebutuhan',
        actor: $this->user
    );

    expect($withdrawEntry->direction)->toBe('keluar')
        ->and($withdrawEntry->amount)->toBe(400000)
        ->and($withdrawEntry->balance_after)->toBe(600000);

    $this->plan->refresh();
    expect($this->plan->balance_cached)->toBe(600000);
    expect($this->ledgerService->calculateActualBalance($this->plan))->toBe(600000);
});

test('BR-01 & BR-03: penarikan melebihi saldo ditolak dengan exception', function () {
    $this->ledgerService->record(
        plan: $this->plan,
        type: LedgerType::SETORAN,
        amount: 200000,
        description: 'Setoran kecil',
        actor: $this->user
    );

    expect(fn () => $this->ledgerService->record(
        plan: $this->plan,
        type: LedgerType::PENARIKAN,
        amount: 500000,
        description: 'Tarik berlebih',
        actor: $this->user
    ))->toThrow(InvalidArgumentException::class);

    $this->plan->refresh();
    expect($this->plan->balance_cached)->toBe(200000);
});

test('BR-01: model LedgerEntry menolak operasi update dan delete', function () {
    $entry = $this->ledgerService->record(
        plan: $this->plan,
        type: LedgerType::SETORAN,
        amount: 500000,
        description: 'Setoran immutable',
        actor: $this->user
    );

    // Attempting update must throw RuntimeException
    expect(fn () => $entry->update(['amount' => 999999]))
        ->toThrow(RuntimeException::class, 'Ledger is append-only');

    // Attempting delete must throw RuntimeException
    expect(fn () => $entry->delete())
        ->toThrow(RuntimeException::class, 'Ledger is append-only');
});

test('BR-02: nominal ledger harus bilangan bulat positif tanpa desimal', function () {
    expect(fn () => $this->ledgerService->record(
        plan: $this->plan,
        type: LedgerType::SETORAN,
        amount: 0,
        description: 'Setor nol',
        actor: $this->user
    ))->toThrow(InvalidArgumentException::class);

    expect(fn () => $this->ledgerService->record(
        plan: $this->plan,
        type: LedgerType::SETORAN,
        amount: -50000,
        description: 'Setor negatif',
        actor: $this->user
    ))->toThrow(InvalidArgumentException::class);
});
