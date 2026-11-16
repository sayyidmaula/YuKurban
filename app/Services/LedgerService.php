<?php

namespace App\Services;

use App\Enums\LedgerType;
use App\Models\LedgerEntry;
use App\Models\SavingsPlan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class LedgerService
{
    /**
     * Record a new append-only ledger transaction with DB lock.
     * Enforces BR-01, BR-02, BR-03.
     */
    public function record(
        SavingsPlan $plan,
        LedgerType $type,
        int $amount,
        string $description,
        ?User $actor = null,
        ?string $refType = null,
        ?int $refId = null,
        ?string $explicitDirection = null
    ): LedgerEntry {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Nominal transaksi ledger harus berupa bilangan bulat positif.');
        }

        return DB::transaction(function () use (
            $plan,
            $type,
            $amount,
            $description,
            $actor,
            $refType,
            $refId,
            $explicitDirection
        ) {
            // Lock the plan record for update (BR-03)
            $lockedPlan = SavingsPlan::where('id', $plan->id)->lockForUpdate()->firstOrFail();

            $direction = $explicitDirection ?? $type->direction();

            if (! in_array($direction, ['masuk', 'keluar'], true)) {
                throw new InvalidArgumentException("Arah transaksi tidak valid: {$direction}");
            }

            $currentBalance = (int) $lockedPlan->balance_cached;

            if ($direction === 'keluar') {
                if ($currentBalance < $amount) {
                    throw new InvalidArgumentException('Saldo tabungan tidak mencukupi untuk penarikan/pemotongan ini.');
                }
                $balanceAfter = $currentBalance - $amount;
            } else {
                $balanceAfter = $currentBalance + $amount;
            }

            // Append-only ledger entry (BR-01)
            $entry = LedgerEntry::create([
                'savings_plan_id' => $lockedPlan->id,
                'user_id' => $lockedPlan->user_id,
                'type' => $type,
                'direction' => $direction,
                'amount' => $amount,
                'balance_after' => $balanceAfter,
                'reference_type' => $refType,
                'reference_id' => $refId,
                'description' => $description,
                'created_by' => $actor ? $actor->id : $lockedPlan->user_id,
                'created_at' => now(),
            ]);

            // Update cached balance inside the exact same transaction
            $lockedPlan->update(['balance_cached' => $balanceAfter]);

            return $entry;
        });
    }

    /**
     * Reconcile balance from all ledger entries.
     */
    public function calculateActualBalance(SavingsPlan $plan): int
    {
        $masuk = (int) LedgerEntry::where('savings_plan_id', $plan->id)
            ->where('direction', 'masuk')
            ->sum('amount');

        $keluar = (int) LedgerEntry::where('savings_plan_id', $plan->id)
            ->where('direction', 'keluar')
            ->sum('amount');

        return $masuk - $keluar;
    }
}

