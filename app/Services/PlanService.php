<?php

namespace App\Services;

use App\Enums\LedgerType;
use App\Models\LedgerEntry;
use App\Models\PriceTier;
use App\Models\SavingsPlan;
use App\Models\User;
use Carbon\Carbon;
use InvalidArgumentException;

class PlanService
{
    public function __construct(
        protected LedgerService $ledgerService,
        protected InvoiceService $invoiceService
    ) {}

    /**
     * Create a new savings plan.
     */
    public function createPlan(
        User $user,
        string $name,
        int $targetAmount,
        int $targetYear,
        int $monthlyAmount,
        ?int $tierId = null,
        bool $autodebetEnabled = false,
        ?int $autodebetDay = null
    ): SavingsPlan {
        if ($targetAmount <= 0) {
            throw new InvalidArgumentException('Target dana tabungan harus lebih dari 0.');
        }

        if ($targetYear < (int) date('Y')) {
            throw new InvalidArgumentException('Tahun target kurban tidak boleh di masa lalu.');
        }

        if ($monthlyAmount <= 0) {
            // Default calculate based on remaining months until June of target year
            $targetDate = Carbon::create($targetYear, 6, 1);
            $monthsRemaining = max(1, now()->diffInMonths($targetDate));
            $monthlyAmount = (int) ceil($targetAmount / $monthsRemaining);
        }

        return SavingsPlan::create([
            'user_id' => $user->id,
            'name' => $name,
            'tier_id' => $tierId,
            'target_amount' => $targetAmount,
            'target_year' => $targetYear,
            'monthly_amount' => $monthlyAmount,
            'status' => 'menabung',
            'autodebet_enabled' => $autodebetEnabled,
            'autodebet_day' => $autodebetDay,
            'balance_cached' => 0,
        ]);
    }

    /**
     * Withdraw savings funds (BR-01, BR-03).
     */
    public function withdraw(
        SavingsPlan $plan,
        int $amount,
        string $reason,
        ?User $actor = null
    ): LedgerEntry {
        if ($plan->status !== 'menabung') {
            throw new InvalidArgumentException('Dana hanya dapat ditarik dari rencana tabungan yang berstatus aktif menabung.');
        }

        return $this->ledgerService->record(
            plan: $plan,
            type: LedgerType::PENARIKAN,
            amount: $amount,
            description: "Penarikan dana tabungan: {$reason}",
            actor: $actor ?? $plan->user,
            refType: SavingsPlan::class,
            refId: $plan->id,
        );
    }

    /**
     * Toggle or configure autodebet settings.
     */
    public function configureAutodebet(SavingsPlan $plan, bool $enabled, ?int $day = null): SavingsPlan
    {
        if ($enabled && ($day < 1 || $day > 28)) {
            $day = min(28, max(1, (int) now()->day));
        }

        $plan->update([
            'autodebet_enabled' => $enabled,
            'autodebet_day' => $enabled ? $day : null,
        ]);

        return $plan->fresh();
    }

    /**
     * Generate daily autodebet invoices for plans due today (06.00).
     */
    public function generateDueAutodebetInvoices(int $currentDay): int
    {
        $plans = SavingsPlan::where('status', 'menabung')
            ->where('autodebet_enabled', true)
            ->where('autodebet_day', $currentDay)
            ->get();

        $generated = 0;
        foreach ($plans as $plan) {
            // Check if there is already a pending invoice from autodebet in the last 24h
            $hasActiveInvoice = $plan->paymentInvoices()
                ->where('status', 'menunggu')
                ->where('source', 'autodebet')
                ->exists();

            if (! $hasActiveInvoice) {
                $this->invoiceService->createInvoice(
                    plan: $plan,
                    amount: $plan->monthly_amount,
                    source: 'autodebet'
                );
                $generated++;
            }
        }

        return $generated;
    }
}
