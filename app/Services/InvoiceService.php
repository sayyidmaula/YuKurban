<?php

namespace App\Services;

use App\Enums\LedgerType;
use App\Models\PaymentInvoice;
use App\Models\SavingsPlan;
use App\Models\User;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class InvoiceService
{
    public function __construct(
        protected LedgerService $ledgerService
    ) {}

    /**
     * Generate unique 16-digit Virtual Account code.
     */
    public function generateVaCode(): string
    {
        do {
            $vaCode = '8888' . str_pad((string) random_int(100000000000, 999999999999), 12, '0', STR_PAD_LEFT);
        } while (PaymentInvoice::where('va_code', $vaCode)->exists());

        return $vaCode;
    }

    /**
     * Create a new simulated payment invoice (valid 24h by default).
     */
    public function createInvoice(
        SavingsPlan $plan,
        int $amount,
        string $source = 'manual',
        int $expiryHours = 24
    ): PaymentInvoice {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Nominal setoran harus lebih besar dari 0.');
        }

        return PaymentInvoice::create([
            'user_id' => $plan->user_id,
            'savings_plan_id' => $plan->id,
            'amount' => $amount,
            'va_code' => $this->generateVaCode(),
            'status' => 'menunggu',
            'expires_at' => now()->addHours($expiryHours),
            'source' => $source,
        ]);
    }

    /**
     * Simulate successful payment.
     */
    public function simulateSuccess(PaymentInvoice $invoice, ?User $actor = null): PaymentInvoice
    {
        if ($invoice->status !== 'menunggu') {
            throw new RuntimeException("Tagihan tidak dapat dibayar karena berstatus {$invoice->status}.");
        }

        if ($invoice->expires_at->isPast()) {
            $invoice->update(['status' => 'kedaluwarsa']);
            throw new RuntimeException('Tagihan virtual account telah kedaluwarsa.');
        }

        // Add ledger entry
        $this->ledgerService->record(
            plan: $invoice->savingsPlan,
            type: LedgerType::SETORAN,
            amount: $invoice->amount,
            description: "Setoran tabungan via VA Simulasi {$invoice->va_code}",
            actor: $actor ?? $invoice->user,
            refType: PaymentInvoice::class,
            refId: $invoice->id,
        );

        $invoice->update([
            'status' => 'berhasil',
            'paid_at' => now(),
        ]);

        return $invoice->fresh();
    }

    /**
     * Simulate failed payment.
     */
    public function simulateFailure(PaymentInvoice $invoice): PaymentInvoice
    {
        if ($invoice->status !== 'menunggu') {
            throw new RuntimeException("Tagihan tidak dalam status menunggu.");
        }

        $invoice->update([
            'status' => 'gagal',
        ]);

        return $invoice->fresh();
    }

    /**
     * Expire pending invoices whose expiry has passed.
     */
    public function expirePendingInvoices(): int
    {
        return PaymentInvoice::where('status', 'menunggu')
            ->where('expires_at', '<=', now())
            ->update(['status' => 'kedaluwarsa']);
    }
}

