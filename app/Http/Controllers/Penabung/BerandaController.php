<?php

namespace App\Http\Controllers\Penabung;

use App\Http\Controllers\Controller;
use App\Models\LedgerEntry;
use App\Models\SavingsPlan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BerandaController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $plans = SavingsPlan::with('tier')
            ->where('user_id', $user->id)
            ->where('status', 'menabung')
            ->get();

        $totalBalance = (int) $plans->sum('balance_cached');

        $activePlans = $plans->map(function ($plan) {
            return [
                'id' => $plan->id,
                'name' => $plan->name,
                'tier' => $plan->tier ? $plan->tier->name : null,
                'target_amount' => $plan->target_amount,
                'target_year' => $plan->target_year,
                'balance_cached' => $plan->balance_cached,
                'progress_percentage' => $plan->progress_percentage,
            ];
        });

        $recentEntries = LedgerEntry::with('savingsPlan')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($entry) {
                return [
                    'id' => $entry->id,
                    'plan_name' => $entry->savingsPlan?->name ?? 'Tabungan',
                    'type' => $entry->type->value,
                    'type_label' => $entry->type->label(),
                    'direction' => $entry->direction,
                    'amount' => $entry->amount,
                    'balance_after' => $entry->balance_after,
                    'description' => $entry->description,
                    'created_date' => $entry->created_at->format('d M Y'),
                    'created_time' => $entry->created_at->format('H:i'),
                ];
            });

        return Inertia::render('Penabung/Beranda', [
            'totalBalance' => $totalBalance,
            'activePlans' => $activePlans,
            'recentEntries' => $recentEntries,
        ]);
    }
}

