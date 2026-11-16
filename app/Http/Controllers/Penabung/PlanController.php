<?php

namespace App\Http\Controllers\Penabung;

use App\Http\Controllers\Controller;
use App\Models\PriceTier;
use App\Models\SavingsPlan;
use App\Services\InvoiceService;
use App\Services\PlanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlanController extends Controller
{
    public function __construct(
        protected PlanService $planService,
        protected InvoiceService $invoiceService
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $plans = SavingsPlan::with('tier')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($plan) {
                return [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'tier' => $plan->tier ? [
                        'id' => $plan->tier->id,
                        'name' => $plan->tier->name,
                    ] : null,
                    'target_amount' => $plan->target_amount,
                    'target_year' => $plan->target_year,
                    'monthly_amount' => $plan->monthly_amount,
                    'status' => $plan->status,
                    'autodebet_enabled' => $plan->autodebet_enabled,
                    'autodebet_day' => $plan->autodebet_day,
                    'balance_cached' => $plan->balance_cached,
                    'progress_percentage' => $plan->progress_percentage,
                ];
            });

        $totalBalance = (int) $plans->sum('balance_cached');

        return Inertia::render('Penabung/Rencana/Index', [
            'plans' => $plans,
            'totalBalance' => $totalBalance,
        ]);
    }

    public function create(): Response
    {
        $tiers = PriceTier::orderBy('sort_order')->get();

        return Inertia::render('Penabung/Rencana/Create', [
            'tiers' => $tiers,
            'defaultYear' => (int) date('Y'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'tier_id' => ['nullable', 'exists:price_tiers,id'],
            'target_amount' => ['required', 'integer', 'min:500000'],
            'target_year' => ['required', 'integer', 'min:' . date('Y'), 'max:' . (date('Y') + 5)],
            'monthly_amount' => ['nullable', 'integer', 'min:50000'],
            'autodebet_enabled' => ['boolean'],
            'autodebet_day' => ['nullable', 'integer', 'min:1', 'max:28'],
        ]);

        $monthlyAmount = ! empty($validated['monthly_amount'])
            ? (int) $validated['monthly_amount']
            : (int) ceil($validated['target_amount'] / 12);

        $plan = $this->planService->createPlan(
            user: $request->user(),
            name: $validated['name'],
            targetAmount: (int) $validated['target_amount'],
            targetYear: (int) $validated['target_year'],
            monthlyAmount: $monthlyAmount,
            tierId: $validated['tier_id'] ?? null,
            autodebetEnabled: (bool) ($validated['autodebet_enabled'] ?? false),
            autodebetDay: $validated['autodebet_day'] ?? null,
        );

        return redirect()->route('rencana.show', $plan->id)
            ->with('success', 'Rencana tabungan kurban berhasil dibuat!');
    }

    public function show(Request $request, SavingsPlan $plan): Response
    {
        if ($plan->user_id !== $request->user()->id) {
            abort(403);
        }

        $plan->load(['tier']);

        $ledgerEntries = $plan->ledgerEntries()
            ->limit(50)
            ->get()
            ->map(function ($entry) {
                return [
                    'id' => $entry->id,
                    'type' => $entry->type->value,
                    'type_label' => $entry->type->label(),
                    'direction' => $entry->direction,
                    'amount' => $entry->amount,
                    'balance_after' => $entry->balance_after,
                    'description' => $entry->description,
                    'created_at' => $entry->created_at->format('d M Y, H:i'),
                ];
            });

        $pendingInvoices = $plan->paymentInvoices()
            ->where('status', 'menunggu')
            ->where('expires_at', '>', now())
            ->get();

        return Inertia::render('Penabung/Rencana/Show', [
            'plan' => [
                'id' => $plan->id,
                'name' => $plan->name,
                'tier' => $plan->tier,
                'target_amount' => $plan->target_amount,
                'target_year' => $plan->target_year,
                'monthly_amount' => $plan->monthly_amount,
                'status' => $plan->status,
                'autodebet_enabled' => $plan->autodebet_enabled,
                'autodebet_day' => $plan->autodebet_day,
                'balance_cached' => $plan->balance_cached,
                'progress_percentage' => $plan->progress_percentage,
            ],
            'ledgerEntries' => $ledgerEntries,
            'pendingInvoices' => $pendingInvoices,
        ]);
    }

    public function setor(Request $request, SavingsPlan $plan): Response
    {
        if ($plan->user_id !== $request->user()->id) {
            abort(403);
        }

        return Inertia::render('Penabung/Rencana/Setor', [
            'plan' => $plan,
        ]);
    }

    public function processSetor(Request $request, SavingsPlan $plan): RedirectResponse
    {
        if ($plan->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:10000'],
        ]);

        $invoice = $this->invoiceService->createInvoice(
            plan: $plan,
            amount: (int) $validated['amount'],
            source: 'manual'
        );

        return redirect()->route('tagihan.show', $invoice->id);
    }

    public function tarik(Request $request, SavingsPlan $plan): Response
    {
        if ($plan->user_id !== $request->user()->id) {
            abort(403);
        }

        return Inertia::render('Penabung/Rencana/Tarik', [
            'plan' => $plan,
        ]);
    }

    public function processTarik(Request $request, SavingsPlan $plan): RedirectResponse
    {
        if ($plan->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:10000', 'max:' . $plan->balance_cached],
            'reason' => ['required', 'string', 'max:255'],
        ], [
            'amount.max' => 'Nominal penarikan tidak boleh melebihi saldo tabungan saat ini.',
        ]);

        $this->planService->withdraw(
            plan: $plan,
            amount: (int) $validated['amount'],
            reason: $validated['reason'],
            actor: $request->user()
        );

        return redirect()->route('rencana.show', $plan->id)
            ->with('success', 'Penarikan dana berhasil dicatat.');
    }

    public function updateAutodebet(Request $request, SavingsPlan $plan): RedirectResponse
    {
        if ($plan->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'autodebet_enabled' => ['required', 'boolean'],
            'autodebet_day' => ['nullable', 'integer', 'min:1', 'max:28'],
        ]);

        $this->planService->configureAutodebet(
            plan: $plan,
            enabled: (bool) $validated['autodebet_enabled'],
            day: $validated['autodebet_day'] ?? null
        );

        return back()->with('success', 'Pengaturan autodebet berhasil diperbarui.');
    }
}

