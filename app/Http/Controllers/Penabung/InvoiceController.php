<?php

namespace App\Http\Controllers\Penabung;

use App\Http\Controllers\Controller;
use App\Models\PaymentInvoice;
use App\Services\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    public function __construct(
        protected InvoiceService $invoiceService
    ) {}

    public function show(Request $request, PaymentInvoice $invoice): Response
    {
        if ($invoice->user_id !== $request->user()->id) {
            abort(403);
        }

        $invoice->load(['savingsPlan']);

        return Inertia::render('Penabung/Tagihan', [
            'invoice' => [
                'id' => $invoice->id,
                'va_code' => $invoice->va_code,
                'amount' => $invoice->amount,
                'status' => $invoice->status,
                'expires_at' => $invoice->expires_at->toIso8601String(),
                'paid_at' => $invoice->paid_at?->toIso8601String(),
                'source' => $invoice->source,
                'is_expired' => $invoice->isExpired(),
                'plan' => [
                    'id' => $invoice->savingsPlan->id,
                    'name' => $invoice->savingsPlan->name,
                ],
            ],
        ]);
    }

    public function simulatePay(Request $request, PaymentInvoice $invoice): RedirectResponse
    {
        if ($invoice->user_id !== $request->user()->id) {
            abort(403);
        }

        try {
            $this->invoiceService->simulateSuccess($invoice, $request->user());

            return redirect()->route('tagihan.show', $invoice->id)
                ->with('success', 'Simulasi pembayaran berhasil! Saldo tabungan Anda telah bertambah.');
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function simulateFail(Request $request, PaymentInvoice $invoice): RedirectResponse
    {
        if ($invoice->user_id !== $request->user()->id) {
            abort(403);
        }

        try {
            $this->invoiceService->simulateFailure($invoice);

            return redirect()->route('tagihan.show', $invoice->id)
                ->with('info', 'Simulasi pembayaran gagal dicatat. Saldo tidak berubah.');
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}

