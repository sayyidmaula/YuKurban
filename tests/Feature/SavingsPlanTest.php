<?php

use App\Models\PriceTier;
use App\Models\SavingsPlan;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->penabung()->create();
    $this->tier = PriceTier::create([
        'name' => 'standar',
        'min_price' => 3500000,
        'max_price' => 6000000,
        'sort_order' => 1,
    ]);
});

test('penabung can view index of savings plans', function () {
    SavingsPlan::create([
        'user_id' => $this->user->id,
        'name' => 'Kurban Domba Standar',
        'tier_id' => $this->tier->id,
        'target_amount' => 4000000,
        'target_year' => (int) date('Y'),
        'monthly_amount' => 350000,
        'status' => 'menabung',
        'balance_cached' => 700000,
    ]);

    $response = $this->actingAs($this->user)->get('/rencana');
    $response->assertStatus(200);
});

test('penabung can create a new savings plan', function () {
    $response = $this->actingAs($this->user)->post('/rencana', [
        'name' => 'Rencana Baru Kurban',
        'tier_id' => $this->tier->id,
        'target_amount' => 4500000,
        'target_year' => (int) date('Y'),
        'monthly_amount' => 375000,
        'autodebet_enabled' => true,
        'autodebet_day' => 15,
    ]);

    $plan = SavingsPlan::where('name', 'Rencana Baru Kurban')->first();
    expect($plan)->not->toBeNull()
        ->and($plan->user_id)->toBe($this->user->id)
        ->and($plan->autodebet_enabled)->toBeTrue()
        ->and($plan->autodebet_day)->toBe(15)
        ->and($plan->balance_cached)->toBe(0);

    $response->assertRedirect(route('rencana.show', $plan->id));
});

test('penabung cannot view other users savings plan', function () {
    $otherUser = User::factory()->penabung()->create();
    $plan = SavingsPlan::create([
        'user_id' => $otherUser->id,
        'name' => 'Rencana Orang Lain',
        'target_amount' => 5000000,
        'target_year' => (int) date('Y'),
        'monthly_amount' => 500000,
        'status' => 'menabung',
        'balance_cached' => 0,
    ]);

    $this->actingAs($this->user)->get("/rencana/{$plan->id}")->assertStatus(403);
    $this->actingAs($this->user)->get("/rencana/{$plan->id}/setor")->assertStatus(403);
    $this->actingAs($this->user)->get("/rencana/{$plan->id}/tarik")->assertStatus(403);
});

test('penabung can withdraw savings funds via form', function () {
    $plan = SavingsPlan::create([
        'user_id' => $this->user->id,
        'name' => 'Rencana Penarikan',
        'target_amount' => 3000000,
        'target_year' => (int) date('Y'),
        'monthly_amount' => 250000,
        'status' => 'menabung',
        'balance_cached' => 0,
    ]);

    // Seed a ledger entry so calculation matches
    app(\App\Services\LedgerService::class)->record(
        plan: $plan,
        type: \App\Enums\LedgerType::SETORAN,
        amount: 1000000,
        description: 'Setoran modal',
        actor: $this->user
    );

    $response = $this->actingAs($this->user)->post("/rencana/{$plan->id}/tarik", [
        'amount' => 300000,
        'reason' => 'Keperluan mendesak',
    ]);

    $response->assertRedirect(route('rencana.show', $plan->id));
    $plan->refresh();
    expect($plan->balance_cached)->toBe(700000);
});

test('penabung can update autodebet configuration', function () {
    $plan = SavingsPlan::create([
        'user_id' => $this->user->id,
        'name' => 'Rencana Autodebet',
        'target_amount' => 3000000,
        'target_year' => (int) date('Y'),
        'monthly_amount' => 250000,
        'status' => 'menabung',
        'autodebet_enabled' => false,
        'balance_cached' => 0,
    ]);

    $this->actingAs($this->user)->post("/rencana/{$plan->id}/autodebet", [
        'autodebet_enabled' => true,
        'autodebet_day' => 20,
    ]);

    $plan->refresh();
    expect($plan->autodebet_enabled)->toBeTrue()
        ->and($plan->autodebet_day)->toBe(20);
});
